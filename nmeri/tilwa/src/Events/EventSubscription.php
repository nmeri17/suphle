<?php
	namespace Tilwa\Events;

	use Tilwa\App\Container;

	use Tilwa\Controllers\{CommandService, RepositoryWrapper};

	// used to ensure all events from each emittable entity are handled by one class per subscriber
	class EventSubscription {

		private $handlingClass;

		private $handlingUnits;
		
		function __construct(string $handlingClass, Container $container) {

			$this->handlingClass = $handlingClass;

			$this->handlingUnits = [];
		}
		
		// since each local event manager points to its own module, we can know that pulling a listener from another module will load the class from its correct scope
		function getHandlingClass():object {

			$container = $this->container;

			$handlingConcrete = $container->getClass($this->handlingClass);
			
			if ($this->handlingClass instanceof CommandService)

				return $container->getClass(RepositoryWrapper::class)->setActiveService($handlingConcrete); // to replicate identical behaviors to directly calling the service i.e fault tolerance etc

			return $handlingConcrete;
		}
		
		function addUnit(string $eventName, string $handlingMethod):void {
			
			$this->handlingUnits[] = new ExecutionUnit($eventName, $handlingMethod);
		}
		
		function getHandlingUnits():array {
			
			return $this->handlingUnits;
		}
	}
?>