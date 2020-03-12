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

		public function findRoute (string $reqPath, int $reqMethod ) {

			$regx = '/\{(\w+)\}/'; $params = [];

			// search register for route matching this pattern
			$target = @array_filter($this->register, function ($route) use (&$params, $regx, $reqPath, $reqMethod) {

				// convert /jui/{fsdf}/weeer to /jui/\w/weeer			
				$tempPat = preg_replace($regx, '\w+', preg_quote($route->pattern) );
				
				$params = preg_grep("/$tempPat/", [$reqPath]); // log all placeholders for the matching pattern

				return preg_match("/^$tempPat$/", $reqPath) && $route->method === $reqMethod;
			});

			$target = current($target);

			/**
			* @see `$this->initParams()` */
			if (!empty($params) ) $params = array_slice($params, 1); // [0]=original url

			$target->parameters = $params;

			return $target;
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

		// serve initial params for this route handler/source
		public function initParams () {

			// get a list of tokens from the actual request (not the pattern)
			return ; // you might break slug at each placeholder point and key each placeholder to the value coming from `$this->parameters`
		}

		public function apiRoutes ($cbGroup ) {

			$this->apiMode = true; // this mode should disable session, template headers etc on each route

			$cbGroup();

			$this->apiMode = null;
		}

		// should accept array or call back to map routes to methods under a source
		public function groupBySource ($cbGroup ) {}
		
		public function registeredRoutes ( ) {

			return $this->register;
		}
	}

?>