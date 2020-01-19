<?php

	namespace Tilwa\Route;

	/**
	 * methods here are what we use to sift routes based off certain criteria
	 */
	class RouteRegister {

		private $prefixMode;
		
		private $register;

		private $namespaceMode;


		function __construct( ) {
		}

		public function register () {

			$args = func_get_args();

			if ( $pref = $this->prefixMode ) $args[0] = $pref . '/'. $args[0];

			if ( $space = $this->namespaceMode ) $args[1] = $space . DIRECTORY_SEPARATOR . $args[1];

			$this->register[] = new Route(...$args);
		}

		public function findRoute ($reqPath, $reqMethod ) {

			$regx = '/[^\/,\d]\{([\w]+)\}/'; $params = [];

			// search register for route matching this pattern
			$target = @array_filter($this->register, function ($route) use (&$params, $regx, $reqPath, $reqMethod) {

				// convert /jui/{fsdf}/weeer to /jui/\w/weeer				
				$tempPat = preg_replace($regx, '\w+', preg_quote($route->pattern) );
				
				$params = preg_grep("/$tempPat/", $reqPath);

				return preg_match("/$tempPat/", $reqPath) && strtolower($route->method) == strtolower($reqMethod);
			});

			$target = current($target);

			if (!empty($target)) $target->parameters = $params;

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
			return ;
		}

		public function redirect ($newPath ) {}
	}

?>