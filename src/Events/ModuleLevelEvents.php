<?php
	namespace Suphle\Events;

	use Suphle\Contracts\{Events, Modules\DescriptorInterface};

	use Suphle\Hydration\Container;

	use Suphle\Modules\Structures\ActiveDescriptors;

	class ModuleLevelEvents {

		protected array $subscriberLog = [],

		$eventManagers = [], // this is where subscribers to the immediate last fired external event reside

		$firedEvents = [];

		public function bootReactiveLogger (ActiveDescriptors $descriptorsHolder):void {

			foreach (
				$descriptorsHolder->getOriginalDescriptors()

				as $descriptor
			) {

				$manager = $container->getClass(Events::class);

				$this->moduleHasListeners($manager, $descriptor->getContainer());
			}
		}

		protected function moduleHasListeners (Events $manager, Container $container):void {

			$manager->setParentManager($this);

			$manager->registerListeners();

			$this->eventManagers[] = $manager;

			$container->whenTypeAny()->needsAny([

				ModuleLevelEvents::class => $this
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