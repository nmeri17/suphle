<?php
	namespace Tilwa\Events;

	use Tilwa\Contracts\Config\Events;

	use Tilwa\Hydration\Container;

	class ModuleLevelEvents {

		private $modules, $subscriberLog = [], // this is where subscribers to the immediate last fired external event reside

		$eventManagers = [], $firedEvents = [];

		public function __construct (array $modules) {

			$this->modules = $modules;
		}

		public function bootReactiveLogger():void {
			
			foreach ($this->modules as $descriptor) {

				$container = $descriptor->getContainer();

				if ($config = $container->getClass(Events::class))

					$this->moduleHasListeners($config, $container);
			}
		}

		protected function moduleHasListeners (Events $config, Container $container):void {

			$manager = $container->getClass($config->getManager());

			$manager->registerListeners();

			$this->eventManagers[] = $manager;

			$container->whenTypeAny()->needsAny([

				EventManager::class => $manager
			]);
		}

		public function gatherForeignSubscribers(string $emittor):self {

			foreach ($this->eventManagers as $manager) {
				
				if ($subscribers = $manager->getExternalHandlers($emittor))

					$this->subscriberLog[] = $subscribers;
			}
			return $this;
		}

		public function triggerExternalHandlers(string $evaluatedModule, string $eventName, $payload):void {

			foreach ($this->subscriberLog as $subscription)

				$this->triggerHandlers($evaluatedModule, $subscription, $eventName, $payload);

			$this->subscriberLog = []; // ahead of next invocation
		}

		public function triggerHandlers (string $sender, ?EventSubscription $subscription, string $eventName, $payload):self {

			$this->firedEvents[$sender] = $subscription; // even though event won't be handled, by logging it all the same, we can verify later that it was emitted

			if (is_null($subscription)) return $this; // no local event handlers attached
			
			$hydratedHandler = $subscription->getHandlingClass();

			foreach ($subscription->getMatchingUnits($eventName) as $unit)
				
				$unit->fire($hydratedHandler, $payload);

			return $this;
		}

		public function getFiredEvents ():array {

			return $this->firedEvents;
		}
	}
?>