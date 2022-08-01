<?php
	namespace Suphle\Events;

	use Suphle\Hydration\Container;

	/**
	 * Used to ensure all events from each emittable entity are handled by one class per subscriber
	*/
	class EventSubscription {

		private $handlingUnits = [], $handlingClass, $container;
		
		public function __construct(string $handlingClass, Container $container) {

			$this->handlingClass = $handlingClass;

			$this->container = $container;
		}
		
		// since each local event manager points to its own module, we can know that pulling a listener from another module will load the class from its correct scope
		public function getHandlingClass ():object {

			return $this->container->getClass($this->handlingClass);
		}
		
		public function addUnit(string $eventName, string $handlingMethod):void {
			
			$this->handlingUnits[] = new ExecutionUnit($eventName, $handlingMethod);
		}
		
		public function getMatchingUnits(string $eventName):array {

			return array_filter($this->handlingUnits, function (ExecutionUnit $unit) use ($eventName) { // return an array of all hits, instead of the first one only so we can chain multiple handlers to one event

				return $unit->matchesEvent($eventName);
			});
		}
	}
?>