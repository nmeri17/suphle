<?php
	namespace Tilwa\Tests\Integration\Exception;

	use Tilwa\Hydration\Container;

	use Tilwa\Exception\Explosives\NotFoundException;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Modules\{ModuleExceptionBridge, ModuleHandlerIdentifier};

	use Tilwa\Response\Format\Json;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Condiments\InvestigateSystemCrash};

	use Throwable, Exception;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use PHPUnit\Framework\MockObject\Stub\Exception as PHPUnitExceptionDouble;

	class TerminatedRequestTest extends ModuleLevelTest {

		use InvestigateSystemCrash;

		private $sutName = ModuleExceptionBridge::class,

		$handlerIdentifier = ModuleHandlerIdentifier::class;

		private function exceptionStubModuleHandler (PHPUnitExceptionDouble $exceptionDouble):ModuleHandlerIdentifier {

			$handler = $this->replaceConstructorArguments($this->handlerIdentifier, [], [
				
				"getModules" => $this->modules,

				"respondFromHandler" => $exceptionDouble,

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

			$container = $this->getContainer();
			
			$this->assertWillCatchPayload($container->getClass(PayloadStorage::class)); // then 1

			$response = "boo!";

			// given
			$container->whenTypeAny()->needsAny([

				$this->sutName => $this->replaceConstructorArguments(
					
					$this->sutName,

					$container->getMethodParameters(Container::CLASS_CONSTRUCTOR, $this->sutName),
					[
					
						"handlingRenderer" => (new Json("actionHandler"))->setRawResponse($response)
					]
				)
			]);

			$entrance = $this->errorStubModuleHandler(function (){

				trigger_error("waterloo", E_USER_ERROR);
			});

			$entrance->diffusedRequestResponse(); // when

			$this->expectOutputString($response); // then 2
		}

		private function bootMockEntrance (ModuleHandlerIdentifier $entrance):void {

			$entrance->bootModules();

			$entrance->extractFromContainer();
		}
	}
?>