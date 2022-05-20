<?php
	namespace Tilwa\Tests\Integration\Modules;

	use Tilwa\Modules\ModuleHandlerIdentifier;

	use Tilwa\Hydration\Container; 

	use Tilwa\Contracts\Auth\ModuleLoginHandler;

	use Tilwa\Flows\OuterFlowWrapper;

	use Tilwa\Testing\Condiments\DirectHttpTest;

	use Tilwa\Tests\Integration\Flows\Jobs\RouteBranches\JobFactory;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	class ModuleHandlerIdentifierTest extends JobFactory {

		use DirectHttpTest;

		protected function getModules():array {

			return [

				new ModuleOneDescriptor(new Container)
			];
		}
		
		public function test_can_handle_login () {

			$sut = $this->getIdentifier();

			$this->stubSingle([

				"getLoginHandler" => $this->mockLoginHandler() // then	
			], $sut); // given

			// when
			$this->setHttpParams("/login", "post", []);

			$sut->respondFromHandler();
		}

		private function mockLoginHandler ():ModuleLoginHandler {

			$handler = $this->negativeDouble(ModuleLoginHandler::class, ["isValidRequest" => true], [

				"processLoginRequest" => [

					$this->atLeastOnce(), []
				]
			]);

			return $handler;
		}

		public function test_saved_flow_triggers_flow_handler () {

			$sut = $this->mockCalls([

				"flowRequestHandler" => [$this->atLeastOnce(), [

					$this->callback(function($argument) {

						return is_a($argument, OuterFlowWrapper::class);
					})
				]]
			], $this->getIdentifier()); // then

			$this->handleDefaultBranchesContext(); // given

			// when
			$this->setHttpParams($this->userUrl);

			$sut->respondFromHandler();
		}

		private function getIdentifier ():ModuleHandlerIdentifier {

			return $this->positiveDouble(ModuleHandlerIdentifier::class, [

				"getModules" => $this->modules
			]);
		}
	}
?>