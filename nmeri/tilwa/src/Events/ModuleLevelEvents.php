<?php
	namespace Tilwa\Events;

	use Tilwa\App\{ModuleDescriptor, Container};

	class ModuleLevelEvents {

		private $subscriberLog = [],

		$eventManagers = [];

		public function bootReactiveLogger(array $descriptors):void {
			
			foreach ($this->descriptors as $descriptor) {

				if ($manager = $descriptor->getEventManager()) {

					$this->eventManagers[] = $manager;

					$manager->registerListeners();

					$descriptor->getContainer()->whenTypeAny()->needsAny([

						EventManager::class => $manager
					]);
				}
			}
		}

		public function gatherForeignSubscribers(string $emittor):self {

			foreach ($this->eventManagers as $manager) {
				
				if ($subscribers = $manager->getExternalHandlers($emittor))

					$this->subscriberLog[] = $subscribers;
			}
			return $this;
		}

		public function triggerExternalHandlers(string $eventName, $payload):void {

			foreach ($this->subscriberLog as $subscription)

				$this->triggerHandlers($subscription, $eventName, $payload);

			$this->subscriberLog = []; // ahead of next invocation
		}

		public function triggerHandlers(EventSubscription $scope, string $eventName, $payload):self {
			
			$hydratedHandler = $scope->getHandlingClass();

			foreach ($scope->getHandlingUnits() as $executionUnit) {
				if ($executionUnit->canExecute($eventName))

					$executionUnit->fire($hydratedHandler, $payload);
			}
			return $this;
		}
	}
?>