<?php

	namespace Nmeri\Tilwa\Route;

	/**
	 * methods here are what we use to sift routes based off certain criteria
	 */
	class RouteRegister {

		private $prefixMode;
		
		private $register;

		private $namespaceMode;

		function __construct( ) {
			
			//
		}

		public function register () {

			$args = func_get_args();

			if ( $pref = $this->prefixMode ) $args[0] = $pref . '/'. $args[0];

			if ( $space = $this->namespaceMode ) $args[1] = $space . DIRECTORY_SEPARATOR . $args[1];

			$this->register[] = new Route(...$args);
		}

		public function findRoute ($reqPath) {

			// search register for route matching this pattern
			array_filter($this->register, function ($route) {
				// 
				$temp = $route->pattern // preg or str replace
			});
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

		public function initParams () {

			// serve initial params for this route handler/source
		}
	}

?>