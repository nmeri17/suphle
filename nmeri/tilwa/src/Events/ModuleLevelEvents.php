<?php
	namespace Tilwa\Events;

	use Tilwa\Contracts\Config\Events;

	class ModuleLevelEvents {

		private $modules, $fireHard = true, $subscriberLog = [], // this is where subscribers to the immediate last fired external event reside

		$eventManagers = [], $blanks = [];

		public function __construct (array $modules) {

			$this->modules = $modules;
		}

		public function bootReactiveLogger():void {
			
			foreach ($this->modules as $descriptor) {

				$container = $descriptor->getContainer();

				if ($config = $container->getClass(Events::class)) {

					$manager = $container->getClass($config->getManager());

					$manager->registerListeners();

					$this->eventManagers[] = $manager;

					$container->whenTypeAny()->needsAny([

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

			if (!$this->fireHard) {

				$this->blanks[] = $scope;

				return $this;
			}
			
			$hydratedHandler = $scope->getHandlingClass();

			foreach ($scope->getMatchingUnits($eventName) as $unit)
				
				$unit->fire($hydratedHandler, $payload);

			return $this;
		}

		public function makeFireSoft ():void {

			$this->fireHard = false;
		}

		public function getBlanks ():array {

			return $this->blanks;
		}
	}
?>