<?php

	namespace Tilwa\Route;

	/**
	 * methods here are what we use to sift routes based off certain criteria
	 */
	class RouteRegister {

		private $prefixMode;
		
		private $register;

		private $namespaceMode;

		private $apiMode;


		function __construct( ) {
		}

		public function register () {

			$args = func_get_args();

			if ( $pref = $this->prefixMode ) $args[0] = $pref . '/'. $args[0];

			if ( $space = $this->namespaceMode ) $args[1] = $space . DIRECTORY_SEPARATOR . $args[1];

			$this->register[] = new Route(...$args);
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

		public function apiRoutes ($cbGroup ) {

			$this->apiMode = true; // this mode should disable session, template headers etc on each route

			$cbGroup();

			$this->apiMode = null;
		}

		// should accept array or call back to map routes to methods under a source
		public function groupBySource ($cbGroup ) {}
		
		public function registeredRoutes () {

			return $this->register;
		}
	}

?>