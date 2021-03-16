<?php

	namespace Tilwa\Controllers;

	use Tilwa\App\Container;

	use Tilwa\Errors\IncompatibleService;

	class Executable {

		private $factoryList;

		private $services;

		private $container;

		public function alternateFactory(string $interface, ...$arguments):object {
			
			$concrete = $this->factoryList[$interface](...$arguments);

			foreach ([self::class, $interface] as $parent)
				
				if (!$concrete instanceof $parent) return null;
			
			return $concrete;
		}

		public function registerFactories() {
			// to be overridden
		}

		/**
		* @desc calls to this goes inside [registerFactories]
		* @param {useCases} class with an [__invoke] method
		*/
		protected function factoryFor(string $interface, string $useCases):self {

			if (is_null($this->factoryList))

				$this->factoryList = [];

			$this->factoryList[$interface] = $useCases;

			return $this;
		}

		# ideally, this should be the only expression in controller's constructor
		protected function loadServices(array $dependencies) {

			$this->services = $dependencies;
		}

		public function validateServices (array $moduleDependencies):bool {
			
			foreach ($this->services as $alias => $service)
				
				if (!$this->isAcceptableService($service, $moduleDependencies))

					throw new IncompatibleService( $alias);
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

		public function injectedEmitter():bool {
			
			foreach ($this->services as $dependency)
				
				if ($dependency instanceof EventManager) return true;
			return false;
		}

		public function __get($property) {

			$concrete = $this->services[$property];
			
			$wrapped = null;

			if ($concrete instanceof EventManager) // ExecutionUnit will eventually wrap the handler in [RepositoryWrapper] if it matches

				$wrapped = $concrete;
			else {
				$container = $this->container;

				if ($concrete instanceof BaseQueryInterceptor)

					$wrapper = $container->getClass(RepositoryWrapper::class);

				else $wrapper = $container->getClass(ServiceWrapper::class);

				$wrapped = $wrapper->setActiveService($concrete);
			}
			return $wrapped;
		}

		public function setContainer(Container $container) {
			
			$this->container = $container;
		}
	}
?>