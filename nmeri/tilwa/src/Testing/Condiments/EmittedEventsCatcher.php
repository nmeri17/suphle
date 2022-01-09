<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Testing\Proxies\Extensions\MockModuleEvents;

	use Tilwa\Modules\ModulesBooter;

	use Tilwa\Events\EventSubscription;

	trait EmittedEventsCatcher {

		private $eventManager;

		/**
		 * We avoid calling parent::setUp here, to circumvent the actual listeners getting bound, which would result in both stubed and real event managers running
		 * 
		 * Tests making use of this trait are expected to only call its [setUp], and not that of [parent]
		*/
		protected function setUp ():void {

			$modules = $this->getModules();

			$this->eventManager = new MockModuleEvents($modules);

			(new ModulesBooter($modules, $this->eventManager))

			->boot();
		}

		abstract protected function getModules ():array;

		protected function assertFiredEvent (string $emitter, string $eventName):void {

			$subscription = $this->findInBlanks($emitter);

			$this->assertNotNull($subscription, "Event '$eventName' not fired");
			
			$this->assertNotEmpty($subscription->getMatchingUnits($eventName));
		}

		protected function assertNotFiredEvent (string $emitter, string $eventName):void {

			$subscription = $this->findInBlanks($emitter);

			if (is_null($subscription)) {

				$this->assertNull($subscription); // to avoid risky test

				return;
			}

			$this->assertEmpty($subscription->getMatchingUnits($eventName), "Event '$eventName' fired by '$emitter'");
		}

		private function findInBlanks (string $sender):?EventSubscription {

			foreach ($this->eventManager->getBlanks() as $subscription)

				if ($subscription->matchesHandler($sender))

					return $subscription;
		}
	}
?>