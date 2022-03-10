<?php
	namespace Tilwa\Tests\Integration\Routing\Nested;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Condiments\MockFacilitator, Proxies\WriteOnlyContainer};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{ Meta\ModuleOneDescriptor, Config\RouterMock, Middlewares\BlankMiddleware};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Prefix\{ActualEntry, MisleadingEntry};

	use Tilwa\Contracts\Config\Router;

	class MultiCollectionsTest extends ModuleLevelTest {

		use MockFacilitator;

		private $threeTierUrl = "/first/middle/third";

		public function setUp ():void {

			$this->frontSetup();
		}

		protected function getModules():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"apiStack" => [

							"v1" => ActualEntry::class,

							"v2" => MisleadingEntry::class
						]
					]);
				})
			];
		}

		public function test_needs_recovery_from_misleading_trail () {

			$this->stubIndicator(); // given

			$this->get($this->threeTierUrl) // when

			// then
			->assertUnauthorized();

			$this->assertUsedMiddleware([BlankMiddleware::class]); 
		}

		private function stubIndicator () {

			$sutName = PatternIndicator::class;

			$this->massProvide([
				$sutName => $this->positiveDouble($sutName, [

					"resetIndications" => null
				])
			]);
		}

		public function test_can_detach_quantities_after_each_entry_collection () {

			$this->get($this->threeTierUrl) // when

			// then
			->assertOk();

			$this->assertDidntUseMiddleware([BlankMiddleware::class]);
		}
	}
?>