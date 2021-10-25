<?php

	namespace Tilwa\Controllers;

	use Tilwa\App\Container;

	use Tilwa\Events\EventManager;

	use Tilwa\Contracts\{QueryInterceptor};

	class Executable {

		private $services, $container, $invalidService,

		$allowedServices;

		# ideally, this should be the only expression in controller's constructor
		protected function loadServices(array $dependencies) {

			$this->services = $dependencies;
		}

		public function hasValidServices (array $moduleDependencies):bool {

			$this->setAllowedServices($moduleDependencies);
			
			foreach ($this->services as $alias => $service)
				
				if (!$this->isAcceptableService($service )) {

					$this->invalidService = $alias;

					return false;
				}
			return true;
		}

		private function isAcceptableService( $dependency):bool {

			foreach ($this->allowedServices as $type)

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

			if ($originalService instanceof QueryInterceptor)

				$wrapper = $container->getClass(RepositoryWrapper::class);

			else $wrapper = $container->getClass(ServiceWrapper::class);

			return $wrapper->setActiveService($originalService);
		}

		private function setAllowedServices ( array $services):void {
			
			$this->allowedServices = [EventManager::class, QueryInterceptor::class, ConditionalFactory::class] + array_map(function ($concrete) {

				return get_class($concrete);
			}, $services);
		}

		public function validatorCollection ():?string {

			return null;
		}
	}
?>