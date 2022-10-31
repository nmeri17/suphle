<?php
	namespace Suphle\Hydration;

	/**
	 * Container helper to load those managers, since at the time class is requested, our container may be unfit to hydrate it by itself
	 */
	class ExternalPackageManagerHydrator {

		private $container, $managers = [];

		public function __construct (Container $container) {

			$this->container = $container;
		}

		/**
		 * Does not decorate objects since we can't have access to decorate those interfaces/entities. Plus, this method is a decorator on its own
		 * 
		 * @return Object, preferably proxied class hydrated from one of the given containers
		*/
		public function findInManagers ( string $fullName ) {

			$activeManager = null;

			foreach ($this->managers as $manager)

				if ( $manager->canProvide($fullName))

					$activeManager = $manager;

			if (is_null($activeManager))

				return null;

			return $activeManager->manageService($fullName);
		}

		/**
		 * @param {managerNames} string<ExternalPackageManager>[]
		*/
		public function setManagers (array $managerNames):void {

			$this->managers = array_map(fn($managerName) => $this->container->getClass($managerName), $managerNames);
		}

		public function hasManagers ():bool {

			return !empty($this->managers);
		}
	}
?>