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

		protected function assertFiredEvent (string $emitter, string $eventName):void {

			$subscription = $this->getEventSender($emitter);

			$this->assertNotNull($subscription,

				"Failed to assert that '$emitter' fired any event"
			);
			
			$this->assertNotEmpty(
				$subscription->getMatchingUnits($eventName),

				"Failed to assert that '$emitter' emitted an event named '$eventName'"
			);
		}

		protected function assertNotFiredEvent (string $emitter, string $eventName):void {

			$subscription = $this->getEventSender($emitter);

			if (is_null($subscription)) {

				$this->assertNull($subscription); // to avoid risky test

				return;
			}

			$this->assertEmpty(
				$subscription->getMatchingUnits($eventName),

				"Did not expect '$emitter' to fire event '$eventName'"
			);
		}

		private function getEventSender (string $sender):?EventSubscription {

			$container = $this->getContainer();

			$config = $container->getClass(Events::class);

			return $container->getClass($config->getManager())->getLocalHandler($sender);
		}
	}
?>