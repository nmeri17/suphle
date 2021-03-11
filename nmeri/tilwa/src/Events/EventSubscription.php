<?php
	namespace Tilwa\Events;

	use Tilwa\App\Container;

	// used to ensure all events from each emittable entity are handled by one class per subscriber
	class EventSubscription {

		private $handlingClass;

		private $handlingUnits;

		private $eventManager;
		
		function __construct(string $handlingClass, Container $container, EventManager $eventManager) {

			$this->handlingClass = $handlingClass;

			$this->handlingUnits = [];

			$this->eventManager = $eventManager;
		}
		
		// since each local event manager points to its own module, we can know that pulling a listener from another module will load the class from its correct scope
		function getHandlingClass():object {
			
			return $this->container->getClass($this->handlingClass);
		}
		
		function addUnit(string $eventName, string $handlingMethod):void {
			
			$this->handlingUnits[] = new ExecutionUnit($eventName, $handlingMethod);
		}
		
		function getHandlingUnits():array {
			
			return $this->handlingUnits;
		}

		public function getEventManager() {
			
			return $this->eventManager;
		}
	}
?>