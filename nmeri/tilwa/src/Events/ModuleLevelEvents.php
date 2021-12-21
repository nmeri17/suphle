<?php
	namespace Tilwa\Events;

	use Tilwa\Contracts\Config\Events;

	use Tilwa\Hydration\Container;

	class ModuleLevelEvents {

		private $modules, $subscriberLog = [], // this is where subscribers to the immediate last fired external event reside

		$eventManagers = [];

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

		public function triggerExternalHandlers(string $eventName, $payload):void {

			foreach ($this->subscriberLog as $subscription)

				$this->triggerHandlers($subscription, $eventName, $payload);

			$this->subscriberLog = []; // ahead of next invocation
		}

		public function triggerHandlers(EventSubscription $scope, string $eventName, $payload):self {
			
			$hydratedHandler = $scope->getHandlingClass();

			foreach ($scope->getMatchingUnits($eventName) as $unit)
				
				$unit->fire($hydratedHandler, $payload);

			return $this;
		}
	}
?>