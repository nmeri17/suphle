<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Testing\Proxies\Extensions\MockModuleEvents;

	use Tilwa\Modules\ModulesBooter;

	use Tilwa\Events\EventSubscription;

	trait EmittedEventsCatcher {

		private $eventManager;

		/**
		 * We avoid calling parent::setUp here, to circumvent the actual listeners getting bound, which would result in both stubed and real event managers running
		*/
		protected function setUp ():void {

			$modules = $this->getModules();

			$this->eventManager = new MockModuleEvents($modules);

			$bootStarter = new ModulesBooter($modules, $this->eventManager);

			$bootStarter->boot();
		}

		abstract protected function getModules ():array;

		protected function assertFiredEvent ($emitter, string $eventName):void {

			$subscription = $this->findInBlanks($emitter);

			$this->assertNotNull($subscription, "Event not fired");
			
			$this->assertNotEmpty($subscription->getMatchingUnits($eventName));
		}

		private function findInBlanks ($sender):?EventSubscription {

			foreach ($this->eventManager->getBlanks() as $subscription)

				if ($subscription->matchesHandler($sender))

					return $subscription;
		}
	}
?>