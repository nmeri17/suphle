<?php
	namespace Tilwa\Tests\Integration\Exception;

	use Tilwa\Hydration\Container;

	use Tilwa\Exception\Explosives\NotFoundException;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Modules\ModuleExceptionBridge;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Condiments\MockFacilitator};

	use Exception;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	class TerminatedRequestTest extends ModuleLevelTest {

		use MockFacilitator;

		private $firstContainer, $sut = ModuleExceptionBridge::class;

		public function setUp ():void {

			$this->firstContainer = new Container;

			parent::setUp();
		}

		/**
		 * @param {exception} mocked Throwable
		*/
		private function exceptionStubModuleHandler ( $exception):ModuleHandlerIdentifier {

			return $this->positiveDouble(ModuleHandlerIdentifier::class, [
				"getModules" => $this->getModules(),

				"respondFromHandler" => $exception
			]);
		}

		private function errorStubModuleHandler (callback $exception):ModuleHandlerIdentifier {

			return $this->positiveDouble(ModuleHandlerIdentifier::class, [
				
				"getModules" => $this->getModules(),

				"respondFromHandler" => $this->returnCallback($callback)
			]);
		}

		protected function getModules ():array {

			return [new ModuleOneDescriptor($this->firstContainer)];
		}

		public function test_exceptions_uses_assigned_handler () {

			$entrance = $this->exceptionStubModuleHandler($this->willThrowException(new NotFoundException)); // given

			$entrance->diffusedRequestResponse(); // when

			$this->assertTrue($entrance->underlyingRenderer()->matchesHandler("missingHandler")); // then
		}

		public function test_exceptions_without_assigned_handler_uses_default () {

			$entrance = $this->exceptionStubModuleHandler($this->willThrowException(new Exception)); // given

			$entrance->diffusedRequestResponse(); // when

			$this->assertTrue($entrance->underlyingRenderer()->matchesHandler("genericHandler")); // then
		}

		public function test_fatal_exception_shutsdown_gracefully () {

			$response = "boo!";

			// given
			$mockSut = $this->positiveDouble($this->sut, [
				
				"handlingRenderer" => (new Json)->setRawResponse($response)
			]);

			$this->firstContainer->whenTypeAny()->needsAny([

				$this->sut => $mockSut
			]);

			$entrance = $this->errorStubModuleHandler(function (){

				trigger_error("waterloo", E_USER_ERROR);
			});

			$this->assertCaughtPayload($this->firstContainer->getClass(PayloadStorage::class)); // then 1

			$entrance->diffusedRequestResponse(); // when

			$this->expectOutputString($response); // then 2
		}
	}
?>