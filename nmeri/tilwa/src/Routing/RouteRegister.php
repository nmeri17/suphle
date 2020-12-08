<?php

	namespace Tilwa\Routing;

	/**
	 * methods here are what we use to sift routes based off certain criteria
	 */
	class RouteRegister {

		private $prefixMode;
		
		private $register;

		private $namespaceMode;

		private $apiMode;

		public function register ():Route {

			$args = func_get_args();

			if ( $pref = $this->prefixMode ) $args[0] = $pref . '/'. $args[0];

			if ( $space = $this->namespaceMode ) $args[1] = $space . DIRECTORY_SEPARATOR . $args[1];

			return $this->register[] = new Route(...$args);
		}

		// every registration within this scope will first be prefixed
		public function prefix ($head, Closure $cbGroup) {

			$this->prefixMode = $head;

			$cbGroup();

			$this->prefixMode = null;
		}

		// every registration within this scope will first be prefixed
		public function namespace ($space, Closure $cbGroup) {

			$this->namespaceMode = $space;

			$cbGroup();

			$this->namespaceMode = null;
		}

		public function apiMirror () {

			// duplicates contents of `register` with disabled session, no template headers, api authentication middleware etc
		}

		// should accept array or call back to map routes to methods under a source
		public function groupBySource ($cbGroup ) {}
		
		public function registeredRoutes () {

			return $this->register;
		}

		/**
		 * assumes specific views exist at those locations
		 *
		 * @param {sourceHandler} when matches our CRUD source class, swap in `model` (meaning it can't be null here). Otherwise, will save the given source + crud method names
		 * @return array
		 **/
		public function crudify (string $baseRoute, string $sourceHandler, /*object*/ $model = null):array {

			$resourceTemplates = [

				// register routes for the methods in `CrudSources`. Only point them those methods if $model is set
			];

			$customize = [];

			foreach ($resourceTemplates as $action => $options)

				$customize[] = $this->register(...$options);

			return $customize; // for dev to modify to taste
		}
	}

?>