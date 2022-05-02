<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Contracts\{Presentation\BaseRenderer, Modules\DescriptorInterface};

	use Tilwa\Hydration\Container;

	use Tilwa\Modules\ModuleExceptionBridge;

	use Tilwa\Exception\DetectedExceptionManager;

	use Tilwa\Testing\Condiments\{ BaseModuleInteractor, ModuleReplicator};

	use Tilwa\Testing\Proxies\Extensions\FrontDoor;

	use PHPUnit\Framework\MockObject\Stub\Stub;

	use Throwable;

	abstract class InvestigateSystemCrash extends TestVirginContainer { // extending from this for sub classes to have access to [dataProvider]

		use BaseModuleInteractor, ModuleReplicator;

		private $shockAbsorber;

		protected $hideShame = false;

		protected function setUp ():void {

			$this->entrance = new FrontDoor ($this->modules = [$this->getModule()]);

			$this->bootMockEntrance($this->entrance);
		}

		protected function getContainer ():Container {

			return $this->firstModuleContainer();
		}

		abstract protected function getModule ():DescriptorInterface;

		/**
		 * Mocks the broadcast alerter
		*/
		protected function assertWillCatchPayload ($payload, callable $flammable):void {

			$this->mockAlerterManager([

				$this->callback(function ($subject) {

					return in_array(Throwable::class, class_implements($subject));
				}),
				$this->equalTo($payload)
			]);

			if ($this->hideShame)

				$this->stubExceptionBridge();

			$this->braceForImpact($flammable);
		}

		private function mockAlerterManager (array $argumentList):void {

			$broadcasterName = DetectedExceptionManager::class;

			$this->getContainer()->whenTypeAny()->needsAny([

				$broadcasterName => $this->replaceConstructorArguments($broadcasterName, [], [], [

					DetectedExceptionManager::ALERTER_METHOD => [1, $argumentList]
				])
			]);
		}

		private function braceForImpact (callable $action):void {

			try {
				
				$action();
			} catch (Throwable $e) {

				$this->setShockAbsorber();

				$this->shockAbsorber->shutdownRites();
			}
		}

		/**
		 * This should first be called if dev wants to directly test custom behavior defined in disgracefulShutdown
		*/
		protected function setShockAbsorber ():void {

			$this->shockAbsorber = $this->getContainer()->getClass(ModuleExceptionBridge::class);
		}

		/**
		 * In order to trigger this, you'd have to violate alerter mock by not calling it, or throwing another error from [gracefulShutdown]
		*/
		private function stubExceptionBridge ():void {

			$bridgeName = ModuleExceptionBridge::class;

			$container = $this->getContainer();

			$parameters = $container->getMethodParameters(Container::CLASS_CONSTRUCTOR, $bridgeName);

			$container->whenTypeAny()->needsAny([

				$bridgeName => $this->replaceConstructorArguments(

					$bridgeName, $parameters, [

					"disgracefulShutdown" => $this->returnCallback(function ($errorDetails, $latestException) {

						throw $latestException;
					}),
					
					"writeStatusCode" => null
				])
			]);
		}

		protected function assertWillCatchException (string $exception, callable $flammable):void {

			$this->mockAlerterManager([

				$this->callback(function ($subject) use ($exception) {

					return $subject instanceof $exception;
				}),
				$this->anything()
			]);

			if ($this->hideShame)

				$this->stubExceptionBridge();

			$this->braceForImpact($flammable);
		}

		protected function assertExceptionUsesRenderer (BaseRenderer $renderer, callable $flammable):void {

			if ($this->hideShame)

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