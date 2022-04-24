<?php
	namespace Tilwa\Tests\Integration\Exception;

	use Tilwa\Hydration\Container;

	use Tilwa\Exception\Explosives\NotFoundException;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Modules\{ModuleExceptionBridge, ModuleHandlerIdentifier};

	use Tilwa\Response\Format\Json;

	use Tilwa\Testing\TestTypes\ModuleLevelTest;

	use Throwable, Exception;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	class TerminatedRequestTest extends ModuleLevelTest {

		private $sutName = ModuleExceptionBridge::class,

		$handlerIdentifier = ModuleHandlerIdentifier::class;

		/**
		 * @param {exception} mocked 
		*/
		private function exceptionStubModuleHandler (Throwable $exception):ModuleHandlerIdentifier {

			$handler = $this->replaceConstructorArguments($this->handlerIdentifier, [], [
				
				"getModules" => $this->modules,

				"respondFromHandler" => $exception,

				"transferHeaders" => null
			], [], true, true, true, true);

			$this->bootMockEntrance($handler);

			return $handler;
		}

		private function errorStubModuleHandler (callable $callback):ModuleHandlerIdentifier {

			$handler = $this->replaceConstructorArguments($this->handlerIdentifier, [], [
				
				"getModules" => $this->modules,

				"respondFromHandler" => $this->returnCallback($callback),

				"transferHeaders" => null
			], [], true, true, true, true);

			$this->bootMockEntrance($handler);

			return $handler;
		}

		protected function getModules ():array {

			return [new ModuleOneDescriptor(new Container)];
		}

		public function test_exceptions_uses_assigned_handler () {

			$entrance = $this->exceptionStubModuleHandler($this->throwException(new NotFoundException)); // given

			$entrance->diffusedRequestResponse(); // when

			$this->assertTrue($entrance->underlyingRenderer()->matchesHandler("missingHandler")); // then
		}

		public function test_exceptions_without_assigned_handler_uses_default () {

			$entrance = $this->exceptionStubModuleHandler($this->throwException(new Exception)); // given

			$entrance->diffusedRequestResponse(); // when

			$this->assertTrue($entrance->underlyingRenderer()->matchesHandler("genericHandler")); // then
		}

		public function test_fatal_exception_shutsdown_gracefully () {

			$response = "boo!";

			$container = $this->getContainer();

			$parameters = $container->getMethodParameters(Container::CLASS_CONSTRUCTOR, $this->sutName);

			// given
			$sut = $this->replaceConstructorArguments($this->sutName, $parameters, [
				
				"handlingRenderer" => (new Json("actionHandler"))->setRawResponse($response)
			]);

			$container->whenTypeAny()->needsAny([

				$this->sutName => $sut
			]);

			$entrance = $this->errorStubModuleHandler(function (){

				trigger_error("waterloo", E_USER_ERROR);
			});
		
// can move this to an instance property
			$this->assertWillCatchPayload($container->getClass(PayloadStorage::class)); // then 1

			$entrance->diffusedRequestResponse(); // when

			$this->expectOutputString($response); // then 2
		}

		private function bootMockEntrance (ModuleHandlerIdentifier $entrance):void {

			$entrance->bootModules();

			$entrance->extractFromContainer();
		}
	}
?>