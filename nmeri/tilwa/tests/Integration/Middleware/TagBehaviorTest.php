<?php
	namespace Tilwa\Tests\Integration\Middleware;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Request\RequestDetails;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Routes\Middlewares\MultiTagSamePattern, Config\RouterMock};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares\{BlankMiddleware, BlankMiddleware2, BlankMiddleware3, BlankMiddleware4};

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	class TagBehaviorTest extends ModuleLevelTest {

		private $moduleOne;

		protected function setUp ():void {

			$this->setModuleOne();

			parent::setUp();
		}

		private function setModuleOne ():void {

			$this->moduleOne = $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

				$container->replaceWithMock(Router::class, RouterMock::class, [

					"browserEntryRoute" => MultiTagSamePattern::class
				]);
			});
		}
		
		protected function getModules():array {

			return [$this->moduleOne];
		}

		private function mockMiddleware (string $className, ?int $numTimes, array $additionalMocks = []) {

			return $this->positiveDouble($className, [], array_merge([

				"process" => [$numTimes, []]
			], $additionalMocks));
		}
 
		public function test_multi_patterns_to_single_tag_should_work () {

			// given => @see [getModules]
			// then 
			$this->moduleOne->getContainer()

			->whenTypeAny()->needsAny([

				BlankMiddleware::class => $this->mockMiddleware(BlankMiddleware::class, 1),

				BlankMiddleware2::class => $this->mockMiddleware(BlankMiddleware2::class, 0)
			]);

			$this->get("/first-single"); // when
		}
 
		public function test_single_pattern_multi_tags_should_work () {

			// given => @see [getModules]
			// then 
			$this->moduleOne->getContainer()

			->whenTypeAny()->needsAny([

				BlankMiddleware::class => $this->mockMiddleware(BlankMiddleware::class, 1),

				BlankMiddleware2::class => $this->mockMiddleware(BlankMiddleware2::class, 1)
			]);

			$this->get("/second-single"); // when
		}
 
		public function test_single_pattern_multi_middleware_should_work () {

			// given => @see [getModules]
			// then 
			$this->moduleOne->getContainer()

			->whenTypeAny()->needsAny([

				BlankMiddleware::class => $this->mockMiddleware(BlankMiddleware::class, 0),

				BlankMiddleware3::class => $this->mockMiddleware(BlankMiddleware3::class, 1),

				BlankMiddleware4::class => $this->mockMiddleware(BlankMiddleware4::class, 1)
			]);

			$this->get("/third-single"); // when
		}

		public function test_parent_tag_affects_child () {

			// given => @see [getModules]
			// then 
			$this->moduleOne->getContainer()

			->whenTypeAny()->needsAny([

				BlankMiddleware::class => $this->mockMiddleware(BlankMiddleware::class, 1),

				BlankMiddleware2::class => $this->mockMiddleware(BlankMiddleware2::class, 0)
			]);

			$this->get("/fifth-single/segment"); // when
		}

		public function test_can_untag_multiple_patterns () {

			// given => @see [getModules]
			// then 
			$this->moduleOne->getContainer()

			->whenTypeAny()->needsAny([

				BlankMiddleware2::class => $this->mockMiddleware(BlankMiddleware2::class, 1),

				BlankMiddleware4::class => $this->mockMiddleware(BlankMiddleware4::class, 0)
			]);

			$this->get("/fourth-single/second-untag"); // when
		}

		public function test_can_untag_multiple_middlewares () {

			// given => @see [getModules]
			// then 
			$this->moduleOne->getContainer()

			->whenTypeAny()->needsAny([

				BlankMiddleware2::class => $this->mockMiddleware(BlankMiddleware2::class, 0),

				BlankMiddleware3::class => $this->mockMiddleware(BlankMiddleware3::class, 0),

				BlankMiddleware4::class => $this->mockMiddleware(BlankMiddleware4::class, 1)
			]);

			$this->get("/fourth-single/third-untag"); // when
		}

		public function test_final_middleware_has_no_request_handler () {

			$middlewareList = $this->moduleOne->getContainer()

			->getClass(Router::class)->defaultMiddleware();

			$lastMiddleware = end($middlewareList);

			$this->moduleOne->getContainer()->whenTypeAny()->needsAny([

				$lastMiddleware => $this->mockMiddleware($lastMiddleware, null, [

					"process" => [1, [

						$this->callback(function ($subject) {

							return $subject instanceof RequestDetails;
						}),

						$this->equalTo(null)
					]]
				]) // then
			]);

			$this->get("/first-single"); // when
		}
	}
?>