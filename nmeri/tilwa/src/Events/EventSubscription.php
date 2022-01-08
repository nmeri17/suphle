<?php
	namespace Tilwa\Events;

	use Tilwa\Hydration\Container;

	/**
	 * Used to ensure all events from each emittable entity are handled by one class per subscriber
	*/
	class EventSubscription {

		private $handlingClass, $handlingUnits, $container;
		
		public function __construct(string $handlingClass, Container $container) {

			$this->handlingClass = $handlingClass;

			$this->container = $container;

			$this->handlingUnits = [];
		}
		
		// since each local event manager points to its own module, we can know that pulling a listener from another module will load the class from its correct scope
		public function getHandlingClass() {

			return $this->container->getClass($this->handlingClass);
		}
		
		public function addUnit(string $eventName, string $handlingMethod):void {
			
			$this->handlingUnits[] = new ExecutionUnit($eventName, $handlingMethod);
		}
		
		public function getMatchingUnits(string $eventName):array {

			return array_filter($this->handlingUnits, function (ExecutionUnit $unit) use ($eventName) {

				return $unit->matchesEvent($eventName);
			});
		}

		public function matchesHandler (string $name):bool {

			return $name == $this->handlingClass;
		}
	}
?>