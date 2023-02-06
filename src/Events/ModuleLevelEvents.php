<?php
	namespace Suphle\Events;

	use Suphle\Contracts\{Events, Modules\DescriptorInterface};

	use Suphle\Hydration\Container;

	use Suphle\Modules\Structures\ActiveDescriptors;

	class ModuleLevelEvents {

		protected array $subscriberLog = [],

		$eventManagers = [], // this is where subscribers to the immediate last fired external event reside

		$firedEvents = [];

		protected ActiveDescriptors $descriptorsHolder;

		public function bootReactiveLogger (ActiveDescriptors $descriptorsHolder):void {

			$this->descriptorsHolder = $descriptorsHolder;

			foreach (
				$descriptorsHolder->getOriginalDescriptors()

				as $descriptor
			) {

				$container = $descriptor->getContainer();

				$container->whenTypeAny()->needsAny([ // bind before hydrating

					ModuleLevelEvents::class => $this
				]);

				$this->eventManagers[] = $manager = $container->getClass(Events::class);

				$manager->registerListeners();
			}
		}

		public function gatherForeignSubscribers(string $emittor):self {

			foreach ($this->eventManagers as $manager) {
				
				if ($subscribers = $manager->getExternalHandlers($emittor))

					$this->subscriberLog[] = $subscribers;
			}
			return $this;
		}

		public function triggerExternalHandlers(string $evaluatedModule, string $eventName, $payload):void {

			foreach ($this->subscriberLog as $subscription) {

				$descriptor = $this->descriptorsHolder->findMatchingExports($subscription->getReceivingModule());

				$descriptor->prepareToRun(); // without this, those modules will still run. However, any custom bindings will not be provided

				$this->triggerHandlers($evaluatedModule, $subscription, $eventName, $payload);
			}

			$this->subscriberLog = []; // ahead of next invocation
		}

		public function triggerHandlers (string $sender, ?EventSubscription $subscription, string $eventName, $payload):self {

			$this->firedEvents[$sender] = $subscription; // even though event won't be handled, by logging it all the same, we can verify later that it was emitted

			if (is_null($subscription)) return $this; // no local event handlers attached
			
			$hydratedHandler = $subscription->getListener();

			foreach ($subscription->getMatchingUnits($eventName) as $unit)
				
				$unit->fire($hydratedHandler, $payload);

			return $this;
		}

		/**
		 * Used only in tests and should be a test-only class but that would hamper DX such that that observer class must be bound during all module builds, since event binding is part of module booting sequence. May be worth it if there were more methods
		*/
		public function getFiredEvents ():array {

			return $this->firedEvents;
		}
	}
?>