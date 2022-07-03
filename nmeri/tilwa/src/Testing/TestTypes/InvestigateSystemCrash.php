<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Contracts\{Presentation\BaseRenderer, Modules\DescriptorInterface};

	use Tilwa\Hydration\{Container, Structures\ObjectDetails};

	use Tilwa\Modules\ModuleExceptionBridge;

	use Tilwa\Exception\DetectedExceptionManager;

	use Tilwa\Testing\Condiments\{ BaseModuleInteractor, ModuleReplicator};

	use Tilwa\Testing\Proxies\{ModuleHttpTest, Extensions\FrontDoor};

	use PHPUnit\Framework\{ ExpectationFailedException, MockObject\Stub\Stub};

	use Throwable;

	abstract class InvestigateSystemCrash extends TestVirginContainer { // extending from this for sub classes to have access to [dataProvider]

		use BaseModuleInteractor, ModuleReplicator, ModuleHttpTest;

		private $shockAbsorber, $objectMeta,

		$bridgeName = ModuleExceptionBridge::class;

		protected $softenDisgraceful = false; // prevents us from stubbing [bridgeName]; we'll use the real one i.e. so that disgracefulShutdown can run

		protected function setUp ():void {

			$this->entrance = new FrontDoor ($this->modules = [$this->getModule()]);

			$this->provideTestEquivalents();

			$this->bootMockEntrance($this->entrance);

			$this->objectMeta = $this->getContainer()->getClass(ObjectDetails::class);
		}

		protected function getContainer ():Container {

			return $this->firstModuleContainer();
		}

		abstract protected function getModule ():DescriptorInterface;

		protected function assertWontCatchPayload ($payload, callable $flammable) {

			return $this->executeHandlerDoubles(0, $payload, $flammable);
		}

		protected function assertWillCatchPayload ($payload, callable $flammable) {

			return $this->executeHandlerDoubles(1, $payload, $flammable);
		}

		/**
		 * 
		 * @return $flammable result
		*/
		private function executeHandlerDoubles (int $numTimes, $payload, callable $flammable) {

			$this->mockBroadcastAlerter($numTimes, [

				$this->anything(), $this->equalTo($payload)
			]);

			if ($this->softenDisgraceful)

				$this->stubExceptionBridge();

			return $this->braceForImpact($flammable);
		}

		/**
		 * @param {argumentList}: Mock verifications for the alerter method
		*/
		private function mockBroadcastAlerter (int $numTimes, array $argumentList):void {

			$broadcasterName = DetectedExceptionManager::class;

			$this->getContainer()->whenTypeAny()->needsAny([

				$broadcasterName => $this->replaceConstructorArguments(

					$broadcasterName, $this->broadcasterArguments(),

					[], [

					DetectedExceptionManager::ALERTER_METHOD => [$numTimes, $argumentList]
				])
			]);
		}

		/**
		 * @return Constructor arguments to use in creating double for DetectedExceptionManager
		*/
		protected function broadcasterArguments ():array {

			return [];
		}

		/**
		 * @return $action result
		*/
		private function braceForImpact (callable $action) {

			try {

				return $action();
			} catch (Throwable $exception) {

				$this->setShockAbsorber();

				$this->shockAbsorber->shutdownRites();
			}
		}

		/**
		 * This should first be called if dev wants to directly test custom behavior defined in disgracefulShutdown
		*/
		protected function setShockAbsorber ():void {

			$this->shockAbsorber = $this->getContainer()->getClass($this->bridgeName);
		}

		/**
		 * What this does is prevent [disgracefulShutdown] from running when [gracefulShutdown] fails. In order for it to be useful, you'd have to violate DetectedExceptionManager::ALERTER_METHOD by not calling it, or throwing another error from [gracefulShutdown]
		*/
		private function stubExceptionBridge ():void {

			$container = $this->getContainer();

			$parameters = $container->getMethodParameters(Container::CLASS_CONSTRUCTOR, $this->bridgeName);

			$container->whenTypeAny()->needsAny([

				$this->bridgeName => $this->replaceConstructorArguments(

					$this->bridgeName, $parameters, [

					"disgracefulShutdown" => $this->returnCallback(function ($errorDetails, $latestException) {

						throw $latestException;
					}),
					
					"writeStatusCode" => null
				])
			]);
		}

		protected function assertWillCatchException (string $exception, callable $flammable):void {

			$this->mockBroadcastAlerter(1, [

				$this->callback(function ($subject) use ($exception) {

					return $this->objectMeta->implementsInterface(get_class($subject), $exception);
				}),
				$this->anything()
			]);

			if ($this->softenDisgraceful)

				$this->stubExceptionBridge();

			$this->braceForImpact($flammable);
		}

		protected function assertExceptionUsesRenderer (BaseRenderer $renderer, callable $flammable):void {

			if ($this->softenDisgraceful)

				$this->stubExceptionBridge();

			try {
				
				$flammable();
			} catch (Throwable $exception) {

				$resolvedRenderer = $this->entrance->findExceptionRenderer($exception);
				
				$this->assertTrue(

					$renderer->matchesHandler($resolvedRenderer->getHandler()),

					"Failed asserting that exception '". get_class($exception) . "' was handled with given renderer"
				);
			}
		}
	}
?>