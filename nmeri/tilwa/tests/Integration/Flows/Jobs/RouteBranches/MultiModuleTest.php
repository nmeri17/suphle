<?php
	namespace Tilwa\Tests\Integration\Flows\Jobs\RouteBranches;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Testing\Proxies\WriteOnlyContainer;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\FlowRoutes, Config\RouterMock};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	class MultiModuleTest extends JobFactory {

		protected function getModules():array {

			return [

				$this->moduleOne, $this->moduleThree
			];
		}

		protected function setModuleOne ():void {

			$this->moduleOne = $this->replicateModule(
				ModuleOneDescriptor::class,

				function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => FlowRoutes::class
					]);
				}
			);
		}
		
		public function test_handle_flows_in_other_modules () {

			$this->get("/flow-to-module3"); // given

			$this->processQueuedTasks(); // when

			$this->assertHandledByFlow("/module-three/5"); // then
		}
	}
?>