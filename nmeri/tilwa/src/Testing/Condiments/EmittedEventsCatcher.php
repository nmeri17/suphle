<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Testing\Proxies\Extensions\MockModuleEvents;

	use Tilwa\Modules\ModulesBooter;

	use Tilwa\Events\{EventSubscription, ModuleLevelEvents};

	trait EmittedEventsCatcher {

		private $eventManager;

		abstract protected function getModules ():array;

		protected function getEventParent ():?ModuleLevelEvents {

			return $this->eventManager = new MockModuleEvents($this->modules);
		}

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