<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Testing\Proxies\Extensions\MockModuleEvents;

	use Tilwa\Contracts\Config\Events;

	use Tilwa\Events\{EventSubscription, ModuleLevelEvents};

	trait EmittedEventsCatcher {

		private $eventParent;

		abstract protected function getModules ():array;

		protected function getEventParent ():?ModuleLevelEvents {

			return $this->eventParent = new MockModuleEvents($this->modules);
		}

		protected function assertFiredEvent (string $emitter):void {

			$this->assertNotNull(
				$this->getEventSubscription($emitter),

				"Failed to assert that '$emitter' fired any event"
			);
		}

		protected function assertHandledEvent (string $emitter, string $eventName):void {

			$subscription = $this->getEventSubscription($emitter);

			$this->assertNotNull($subscription,

				"Failed to assert that '$emitter' fired any event"
			);
			
			$this->assertNotEmpty(
				$subscription->getMatchingUnits($eventName),

				"Failed to assert that '$emitter' emitted an event named '$eventName'"
			);
		}

		protected function assertNotFiredEvent (string $emitter):void {

			$this->assertNull(

				$this->getEventSubscription($emitter),

				"Did not expect '$emitter' to fire event"
			);
		}

		protected function assertNotHandledEvent (string $emitter, string $eventName):void {

			$subscription = $this->getEventSubscription($emitter);

			if (is_null($subscription)) {

				$this->assertNull($subscription); // to avoid risky test

				return;
			}

			$this->assertEmpty(
				$subscription->getMatchingUnits($eventName),

				"Did not expect '$emitter' to fire event '$eventName'"
			);
		}

		private function getEventSubscription (string $sender):?EventSubscription {

			$allSent = $this->eventParent->getFiredEvents();

			if (array_key_exists($sender, $allSent)) {

				$subscription = $allSent[$sender];

				if (is_null($subscription)) // was event fired without any paired handlers?
					$subscription = new EventSubscription("", $this->getContainer()); // so the asserters don't mistake false positive

				return $subscription;
			}

			return null;
		}
	}
?>