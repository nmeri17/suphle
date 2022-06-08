<?php
	namespace Tilwa\Tests\Integration\Flows;

	use Tilwa\Flows\OuterFlowWrapper;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Testing\{Proxies\WriteOnlyContainer, Condiments\EmittedEventsCatcher};

	use Tilwa\Tests\Integration\Flows\Jobs\RouteBranches\JobFactory;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\FlowRoutes, Meta\ModuleOneDescriptor, Config\RouterMock};

	class OuterFlowWrapperTest extends JobFactory {

		use EmittedEventsCatcher;

		protected function getModules():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => FlowRoutes::class
					]);
				})
			];
		}
 
		public function test_will_queueBranches_after_returning_flow_request() {

			// given
			$this->originDataName = "flow_models";

			$this->flowUrl = "/initial-flow/id";

			$this->handleDefaultBranchesContext(); // when

			$this->assertPushedToFlow("/flow-with-flow/5"); // then
		}
	}
?>