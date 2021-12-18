<?php
	namespace Tilwa\Tests\Integration\App;

	use Tilwa\Tests\Integration\Flows\Jobs\RouteBranches\JobFactory;

	use Tilwa\Testing\Condiments\DirectHttpTest;

	use Tilwa\App\{Container, ModuleHandlerIdentifier};

	use Tilwa\Auth\LoginRequestHandler;

	use Tilwa\Flows\OuterFlowWrapper;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\ModuleOneDescriptor;

	class ModuleHandlerIdentifierTest extends JobFactory {

		use DirectHttpTest;

		protected function getModules():array {

			return [

				new ModuleOneDescriptor(new Container)
			];
		}
		
		public function test_can_handle_login () {

			$sut = $this->getIdentifier();

			$sut->expects($this->atLeastOnce())->method("getLoginHandler")
			
			->will(
				$this->returnValue($this->mockLoginHandler()) // then
			); // given

			// when
			$this->setHttpParams("/login", "post", []);

			$sut->beginRequest();
		}

		private function mockLoginHandler ():LoginRequestHandler {

			$handler = $this->negativeStub(LoginRequestHandler::class, ["isValidRequest" => true]);

			$handler->expects($this->atLeastOnce())->method("getResponse")
			
			->with( $this->anything() );

			return $handler;
		}

		public function test_saved_flow_triggers_flow_handler () {

			$sut = $this->getIdentifier();

			$sut->expects($this->atLeastOnce())->method("flowRequestHandler")
			
			->with( $this->callback(function($argument) {

				return is_a($argument, OuterFlowWrapper::class);
			})); // then

			$this->makeJob($this->makeBranchesContext(null))->handle(); // given

			// when
			$this->setHttpParams($this->userUrl);

			$sut->beginRequest();
		}

		private function getIdentifier ():ModuleHandlerIdentifier {

			return $this->positiveStub(ModuleHandlerIdentifier::class, [

				"getModules" => $this->getModules()
			]);
		}
	}
?>