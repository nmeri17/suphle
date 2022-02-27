<?php
	namespace Tilwa\Tests\Unit\Modules;

	use Tilwa\Modules\ModuleHandlerIdentifier;

	use Tilwa\Exception\Explosives\ValidationFailure;

	use Tilwa\Contracts\Auth\ModuleLoginHandler;

	use Tilwa\Testing\{TestTypes\IsolatedComponentTest, Condiments\MockFacilitator};

	class ModuleHandlerIdentifierTest extends IsolatedComponentTest {

		use MockFacilitator;
		
		public function test_validation_failure_on_login_will_terminate () {

			$this->setExpectedException(ValidationFailure::class); // then

			$moduleHandler = new class extends ModuleHandlerIdentifier {

				public function extractFromContainer ($loginHandler = null) { // for it to be compatible with parent

					$this->loginHandler = $loginHandler;
				}
			};

			$loginHandlerDouble = $this->negativeStub(ModuleLoginHandler::class, [

				"isValidRequest" => false
			]);

			$sut = new $moduleHandler;

			$sut->extractFromContainer($loginHandlerDouble); // given

			$sut->handleLoginRequest(); // when
		}
	}
?>