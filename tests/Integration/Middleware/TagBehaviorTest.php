<?php
	namespace Suphle\Tests\Integration\Middleware;

	use Suphle\Contracts\{Config\Router, Routing\Middleware};

	use Suphle\Request\PayloadStorage;

	use Suphle\Response\Format\Json;

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Routes\Middlewares\MultiTagSamePattern, Config\RouterMock};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares\{BlankMiddleware, BlankMiddleware2, BlankMiddleware3, BlankMiddleware4};

	class TagBehaviorTest extends ModuleLevelTest {

		private $container;

		protected function setUp ():void {

			parent::setUp();

			$this->container = $this->getContainer();
		}
		
		protected function getModules():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => MultiTagSamePattern::class
					]);
				})
			];
		}

		// without this, we'll use getModules and then need to have test classes for each of these different configurations
		private function mockMiddleware (string $className, int $numTimes):Middleware {

			return $this->positiveDouble($className, [

				"process" => $this->returnCallback(function($request, $requestHandler) {

					return $requestHandler->handle($request);
				})
			], [

				"process" => [$numTimes, []]
			]);
		}

		private function provideMiddleware (array $middlewareList):void {

			$this->container->whenTypeAny()->needsAny($middlewareList);
		}
 
		public function test_multi_patterns_to_single_tag_should_work () {

			// given => @see [getModules]
			// then 
			$this->provideMiddleware([

				BlankMiddleware::class => $this->mockMiddleware(BlankMiddleware::class, 1),

				BlankMiddleware2::class => $this->mockMiddleware(BlankMiddleware2::class, 0)
			]);

			$this->get("/first-single"); // when
		}
 
		public function test_single_pattern_multi_tags_should_work () {

			// given => @see [getModules]
			// then 
			$this->provideMiddleware([

				BlankMiddleware::class => $this->mockMiddleware(BlankMiddleware::class, 1),

				BlankMiddleware2::class => $this->mockMiddleware(BlankMiddleware2::class, 1)
			]);

			$this->get("/second-single"); // when
		}
 
		public function test_single_pattern_multi_middleware_should_work () {

			// given => @see [getModules]
			// then 
			$this->provideMiddleware([

				BlankMiddleware::class => $this->mockMiddleware(BlankMiddleware::class, 0),

				BlankMiddleware3::class => $this->mockMiddleware(BlankMiddleware3::class, 1),

				BlankMiddleware4::class => $this->mockMiddleware(BlankMiddleware4::class, 1)
			]);

			$this->get("/third-single"); // when
		}

		public function test_parent_tag_affects_child () {

			// given => @see [getModules]
			// then 
			$this->provideMiddleware([

				BlankMiddleware::class => $this->mockMiddleware(BlankMiddleware::class, 1),

				BlankMiddleware2::class => $this->mockMiddleware(BlankMiddleware2::class, 0)
			]);

			$this->get("/fifth-single/segment"); // when
		}

		public function test_can_untag_multiple_patterns () {

			// given => @see [getModules]
			// then 
			$this->provideMiddleware([

				BlankMiddleware2::class => $this->mockMiddleware(BlankMiddleware2::class, 1),

				BlankMiddleware4::class => $this->mockMiddleware(BlankMiddleware4::class, 0)
			]);

			$this->get("/fourth-single/second-untag"); // when
		}

		public function test_can_untag_multiple_middlewares () {

			// given => @see [getModules]
			// then 
			$this->provideMiddleware([

				BlankMiddleware2::class => $this->mockMiddleware(BlankMiddleware2::class, 0),

				BlankMiddleware3::class => $this->mockMiddleware(BlankMiddleware3::class, 0),

				BlankMiddleware4::class => $this->mockMiddleware(BlankMiddleware4::class, 1)
			]);

			$this->get("/fourth-single/third-untag"); // when
		}

		public function test_final_middleware_has_no_request_handler () {

			$middlewareList = $this->container->getClass(Router::class)

			->defaultMiddleware();

			$lastMiddleware = end($middlewareList);

			$this->provideMiddleware([

				$lastMiddleware => $this->positiveDouble($lastMiddleware, [

					"process" => $this->replaceConstructorArguments(Json::class, [])
				], [

					"process" => [1, [

						$this->callback(function ($subject) {

							return $subject instanceof PayloadStorage;
						}),

						$this->equalTo(null)
					]]
				]) // then
			]);

			$this->get("/first-single"); // when
		}
	}
?>