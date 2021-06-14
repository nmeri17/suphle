<?php

	namespace Tilwa\Controllers;

	use Tilwa\App\Container;

	use Tilwa\Events\EventManager;

	class Executable {

		private $services, $container, $invalidService;

		# ideally, this should be the only expression in controller's constructor
		protected function loadServices(array $dependencies) {

			$this->services = $dependencies;
		}

		public function hasValidServices (array $moduleDependencies):bool {
			
			foreach ($this->services as $alias => $service)
				
				if (!$this->isAcceptableService($service, $moduleDependencies)) {

					$this->invalidService = $alias;

					return false;
				}
			return true;
		}

		private function isAcceptableService(object $dependency, array $foreignServices):bool {
			
			$allowed = [EventManager::class, BaseQueryInterceptor::class, NoSqlLogic::class] + array_map(function ($concrete) {

				return $concrete::class;
			}, $foreignServices);

			foreach ($allowed as $type)

				if ($dependency instanceof $type) return true;

			return false;
		}

		public function hasIsolatedConstructor():bool {
			
			return empty(get_object_vars($this));
		}

		public function __get($property) {

			$concrete = $this->services[$property];

			if ($concrete instanceof EventManager)

				return $concrete; // [ExecutionUnit] will eventually wrap the handler in [RepositoryWrapper] if it matches
			$this->setupBootableService($concrete);

			return $this->getWrappedService($concrete);
		}

		public function setContainer(Container $container):self {
			
			$this->container = $container;

			return $this;
		}

		public function getInvalidService():string {
			
			return $this->invalidService;
		}

		private function getWrappedService(object $originalService) {
			
			$container = $this->container;

			if ($originalService instanceof BaseQueryInterceptor)

				$wrapper = $container->getClass(RepositoryWrapper::class);

			else $wrapper = $container->getClass(ServiceWrapper::class);

			return $wrapper->setActiveService($originalService);
		}

		// A better location for this would've been while setting it in the service wrapper? But it seems like too little a reason to pass in the container
		private function setupBootableService(object $concrete):void {
			
			if ($concrete instanceof BootsService)

				$concrete->setup($this->container); 
		}
	}
?>