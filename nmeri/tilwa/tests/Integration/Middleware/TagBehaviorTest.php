<?php
	namespace Tilwa\Tests\Integration\Middleware;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Request\RequestDetails;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Routes\Middlewares\MultiTagSamePattern, Config\RouterMock};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares\{BlankMiddleware, BlankMiddleware2, BlankMiddleware3, BlankMiddleware4};

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Prophecy\Argument;

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
 
		public function test_multi_patterns_to_single_tag_should_work () {

			$blank1 = $this->prophesize(BlankMiddleware::class)

			->process()->shouldBeCalled();

			$blank2 = $this->prophesize(BlankMiddleware2::class)

			->process()->shouldNotBeCalled();

			// given => @see [getModules]
			// then 
			$this->moduleOne->getContainer()

			->whenTypeAny()->needsAny([

				BlankMiddleware::class => $blank1->reveal(),

				BlankMiddleware2::class => $blank2->reveal()
			]);

			$this->get("/first-single"); // when
		}
 
		public function test_single_pattern_multi_tags_should_work () {

			$blank1 = $this->prophesize(BlankMiddleware::class)

			->process()->shouldBeCalled();

			$blank2 = $this->prophesize(BlankMiddleware2::class)

			->process()->shouldBeCalled();

			// given => @see [getModules]
			// then 
			$this->moduleOne->getContainer()

			->whenTypeAny()->needsAny([

				BlankMiddleware::class => $blank1->reveal(),

				BlankMiddleware2::class => $blank2->reveal()
			]);

			$this->get("/second-single"); // when
		}
 
		public function test_single_pattern_multi_middleware_should_work () {

			$blank1 = $this->prophesize(BlankMiddleware::class)

			->process()->shouldNotBeCalled();

			$blank3 = $this->prophesize(BlankMiddleware3::class)

			->process()->shouldBeCalled();

			$blank4 = $this->prophesize(BlankMiddleware4::class)

			->process()->shouldBeCalled();

			// given => @see [getModules]
			// then 
			$this->moduleOne->getContainer()

			->whenTypeAny()->needsAny([

				BlankMiddleware::class => $blank1->reveal(),

				BlankMiddleware3::class => $blank3->reveal(),

				BlankMiddleware4::class => $blank4->reveal()
			]);

			$this->get("/third-single"); // when
		}

		public function test_parent_tag_affects_child () {

			$blank1 = $this->prophesize(BlankMiddleware::class)

			->process()->shouldBeCalled();

			$blank2 = $this->prophesize(BlankMiddleware2::class)

			->process()->shouldNotBeCalled();

			// given => @see [getModules]
			// then 
			$this->moduleOne->getContainer()

			->whenTypeAny()->needsAny([

				BlankMiddleware::class => $blank1->reveal(),

				BlankMiddleware2::class => $blank2->reveal()
			]);

			$this->get("/fifth-single/segment"); // when
		}

		public function test_can_untag_multiple_patterns () {

			$blank2 = $this->prophesize(BlankMiddleware2::class)

			->process()->shouldBeCalled();

			$blank4 = $this->prophesize(BlankMiddleware4::class)

			->process()->shouldNotBeCalled();

			// given => @see [getModules]
			// then 
			$this->moduleOne->getContainer()

			->whenTypeAny()->needsAny([

				BlankMiddleware2::class => $blank2->reveal(),

				BlankMiddleware4::class => $blank4->reveal()
			]);

			$this->get("/fourth-single/second-untag"); // when
		}

		public function test_can_untag_multiple_middlewares () {

			$blank2 = $this->prophesize(BlankMiddleware2::class)

			->process()->shouldNotBeCalled();

			$blank3 = $this->prophesize(BlankMiddleware3::class)

			->process()->shouldNotBeCalled();

			$blank4 = $this->prophesize(BlankMiddleware4::class)

			->process()->shouldBeCalled();

			// given => @see [getModules]
			// then 
			$this->moduleOne->getContainer()

			->whenTypeAny()->needsAny([

				BlankMiddleware2::class => $blank2->reveal(),

				BlankMiddleware3::class => $blank3->reveal(),

				BlankMiddleware4::class => $blank4->reveal()
			]);

			$this->get("/fourth-single/third-untag"); // when
		}

		public function test_final_middleware_has_no_request_handler () {

			$middlewareList = $this->moduleOne

			->getContainer()->getClass(Router::class)

			->defaultMiddleware();

			$lastMiddleware = end($middlewareList);
			
			$mockMiddleware = $this->prophesize($lastMiddleware)

			->process(Argument::type(RequestDetails::class), Argument::exact(null)); // then

			$this->moduleOne->getContainer()

			->whenTypeAny()->needsAny([

				$lastMiddleware => $mockMiddleware->reveal()
			]);

			$this->get("/first-single"); // when
		}
	}
?>