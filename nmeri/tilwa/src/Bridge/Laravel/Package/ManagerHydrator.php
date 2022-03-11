<?php
	namespace Tilwa\Bridge\Laravel\Package;

	use Tilwa\Hydration\Container;

	class ManagerHydrator {

		private $container, $isInitializing, $manager;

		public function __construct (Container $container) {

			$this->container = $container;
		}

		/**
		 * Does not decorate objects since we can't have access to decorate those interfaces/entities. Plus, this method is a decorator on its own
		*/
		public function loadPackage ( string $fullName ) {

			$manager = $this->getManager();

			if (is_null($manager) || !$manager->canProvide($fullName))

				return null;

			return $manager->manageService($fullName);
		}

		/**
		 * @return null when attempting to hydrate its own dependencies
		*/
		public function getManager ():?LaravelProviderManager {

			$managerName = LaravelProviderManager::class;

			if (!$this->isInitializing && is_null($this->manager)) { // prevent recursive loop on the container

				$this->isInitializing = true;

				$this->manager = $this->container->instantiateConcrete($managerName);

				$this->isInitializing = false;
			}

			return $this->manager;
		}
	}
?>