<?php
	namespace Tilwa\Tests\Integration\Flows;

	use Tilwa\Flows\OuterFlowWrapper;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Testing\Proxies\WriteOnlyContainer;

	use Tilwa\Tests\Integration\Flows\Jobs\RouteBranches\JobFactory;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\FlowRoutes, ModuleOneDescriptor, Config\RouterMock};

	class OuterFlowWrapperTest extends JobFactory {

		protected function getModules():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => FlowRoutes::class
					]);
				})
			];
		}

		public function test_will_emitEvents_after_returning_flow_request() {

			$this->handleFlowJob();

			$this->get($this->userUrl); // when

			$this->assertFiredEvent ($this->rendererController, OuterFlowWrapper::HIT_EVENT); // then
		}
 
		public function test_will_queueBranches_after_returning_flow_request() {

			// given
			$this->originDataName = "flow_models";

			$this->flowUrl = "/initial-flow/id";

			$this->handleFlowJob(); // when

			$this->assertPushedToFlow("/flow-with-flow/5"); // then
		}

		private function handleFlowJob ():void {

			$this->makeJob($this->makeBranchesContext(null))->handle();
		}
	}
?>