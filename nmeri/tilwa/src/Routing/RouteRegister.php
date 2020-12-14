<?php

	namespace Tilwa\Routing;

	class RouteRegister {

		private $prefixMode;
		
		private $register;

		private $namespaceMode;

		private $apiMode;

		public function register (Route $route) { // review this method

			$args = func_get_args();

			if ( $pref = $this->prefixMode ) $args[0] = $pref . '/'. $args[0]; // the route object should provide methods for updating these

			if ( $space = $this->namespaceMode ) $args[1] = $space . DIRECTORY_SEPARATOR . $args[1];

			$route = new Route(...$args);

			$route->setHandler(); // this now takes app as argument

			$this->register[] = $route;
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

			// duplicates contents of `register`, creating Json routes for all of them; api authentication middleware etc
		}

		// overrides can include accept header for dictating route type
		public function crud (string $basePath, string $controller, array $overrides ) {

			// there should be an overwriteable heuristic for determining whether view for a requested exists and to return that or JSON (along with what controller action we're calling)

			$resourceTemplates = []; // showCreateForm, saveNew, showAll, showOne, update, delete

			// foreach ($resourceTemplates)
		}
		
		public function registeredRoutes () {

			return $this->register;
		}
	}
?>