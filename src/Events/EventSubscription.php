<?php
	namespace Suphle\Events;

	use Suphle\Hydration\Container;

	/**
	 * Used to ensure all events from each emittable entity are handled by one class per subscriber
	*/
	class EventSubscription {

		protected array $handlingUnits = [];
		
		public function __construct(protected readonly string $handlingClass, protected readonly Container $container) {

			//
		}
		
		// since each local event manager points to its own module, we can know that pulling a listener from another module will load the class from its correct scope
		public function getHandlingClass ():object {

			return $this->container->getClass($this->handlingClass);
		}
		
		public function addUnit(string $eventName, string $handlingMethod):void {
			
			$this->handlingUnits[] = new ExecutionUnit($eventName, $handlingMethod);
		}
		
		public function getMatchingUnits(string $eventName):array {

			return array_filter($this->handlingUnits, fn(ExecutionUnit $unit) => // return an array of all hits, instead of the first one only so we can chain multiple handlers to one event
$unit->matchesEvent($eventName));
		}
	}
?>