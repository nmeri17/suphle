<?php
	namespace Suphle\Tests\Integration\Flows;

	use Suphle\Flows\OuterFlowWrapper;

	use Suphle\Contracts\Config\Router;

	use Suphle\Testing\{Proxies\WriteOnlyContainer, Condiments\EmittedEventsCatcher};

	use Suphle\Tests\Integration\Flows\Jobs\RouteBranches\JobFactory;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\FlowRoutes, Meta\ModuleOneDescriptor, Config\RouterMock};

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

			$this->handleDefaultPendingFlowDetails(); // when

			$this->assertPushedToFlow("/flow-with-flow/5"); // then
		}
	}
?>