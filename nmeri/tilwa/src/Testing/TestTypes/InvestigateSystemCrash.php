<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Contracts\Presentation\BaseRenderer;

	use Tilwa\Hydration\Container;

	use Tilwa\Modules\ModuleHandlerIdentifier;

	use Tilwa\Testing\Condiments\{MockFacilitator, BaseModuleInteractor, ModuleReplicator};

	use Tilwa\Testing\Proxies\{GagsException, Extensions\FrontDoor};

	use PHPUnit\Framework\MockObject\Stub\Stub;

	use Throwable;

	abstract class InvestigateSystemCrash extends TestVirginContainer { // extending from this for sub classes to have access to [dataProvider]

		use GagsException, BaseModuleInteractor, ModuleReplicator {

			GagsException::setUp as mufflerSetup;
		}

		private $alerterMethod = "queueAlertAdapter",

		$disgracefulShutdown = "disgracefulShutdown";

		protected function setUp ():void {

			$entrance = new FrontDoor ($this->modules = $this->getModules());

			$this->bootMockEntrance($entrance);

			$this->mufflerSetup();
		}

		abstract protected function getModules ():array;

		protected function getContainer ():Container {

			return $this->modules[0]->getContainer();
		}

		protected function exceptionModuleHandler (Stub $exceptionalConduct):ModuleHandlerIdentifier {

			$handlerIdentifier = $this->replaceConstructorArguments(ModuleHandlerIdentifier::class, [], [
				
				"getModules" => $this->modules,

				"respondFromHandler" => $exceptionalConduct,

				"transferHeaders" => null
			], [], true, true, true, true);

			$this->bootMockEntrance($handlerIdentifier);

			return $handlerIdentifier;
		}

		protected function assertWillCatchPayload ($payload):void {

			$this->mockCalls([

				$this->alerterMethod => [1, [

					$this->callback(function ($subject) {

						return in_array(Throwable::class, class_implements($subject));
					}),
					$this->equalTo($payload)
				]]
			], $this->exceptionBroadcaster);

			$this->provideExceptionBridge($this->defaultBridgeStubs()); // trigger a new one to replace [exceptionBridge] stubs
		}

		protected function defaultBridgeStubs ():array {

			return [
				
				$this->disgracefulShutdown => $this->getDisgracefulShutdown()
			];
		}

		protected function assertWillCatchException (string $exception):void {

			$this->mockCalls([

				$this->alerterMethod => [1, [

					$this->callback(function ($subject) use ($exception) {

						return $subject instanceof $exception;
					}),
					$this->anything()
				]]
			], $this->exceptionBroadcaster);

			$this->provideExceptionBridge($this->defaultBridgeStubs());
		}

		protected function assertExceptionUsesRenderer (BaseRenderer $renderer):void {

			$this->assertEquals(

				$renderer, $this->exceptionBridge->handlingRenderer(),

				"Failed asserting that caught exception was handled with given renderer"
			); // then
		}
	}
?>