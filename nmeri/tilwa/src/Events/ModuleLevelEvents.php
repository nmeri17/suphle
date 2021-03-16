<?php
	namespace Tilwa\Events;

	use Tilwa\App\ParentModule;

	class ModuleLevelEvents {

		private $subscriberLog;

		private $eventManagers;

		function __construct () {

			$this->eventManagers = [];
		}

		public function bootReactiveLogger(array $modules):void {
			
			foreach ($this->modules as $module)
				
				$this->setEventManager($module);
		}

		private function setEventManager(ParentModule $module):void {

			$manager = $this->eventManagers[] = new EventManager($module, $this);

			$manager->registerListeners();

			$module->container->whenTypeAny()->needsAny([

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