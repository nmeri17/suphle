<?php

	namespace Tilwa\Route;

	use Exception;

	class Route {

		public $queryVars;

		public $pattern;

		public $parameters;

		public $method;

		public $viewName;

		private $middleware;


		// setting `viewName` to false skips the trip to parse
		// setting it to null assigns the name of your source handler to it
		function __construct(

			string $pathPattern, string $source = null, string $viewName = null,

			$method = 'get', $appendHeader = true, $middleware = []
		) {

			$this->hasQuery();

			$this->validateSource($source);

			$this->assignView($viewName);


			$this->middleware = is_string($middleware) ? [$middleware] : $middleware;

			$this->appendHeader = $appendHeader;

			$this->pattern = !strlen($pathPattern) ? 'index' : $pathPattern;

			$this->method = strtolower($method);
		}

		private function hasQuery () {

			$this->queryVars = array_filter($_GET, function ( $key) {

				return $key !== 'tilwa_request';
			}, ARRAY_FILTER_USE_KEY);
		}

		private function validateSource ( $src ) {

			// 1st confirm a source was given to begin with. If none, assume path doesn't include dynamic vars
			if ( !is_null($src) ) {

				if ( preg_match('/([\w\\\\]+@\w+)/', $src, $res) ) $this->source = $src;

				else throw new Exception("Invalid source pattern given" );
			}
		}

		public function getMiddlewares () {

			return $this->middleware;
		}

		private function assignView ( $name ) {

			if (!is_null($name)) $this->viewName = $name;

			elseif ( $source = $this->source ) $this->viewName = explode('@', $source)[1];

			if (!$this->viewName) throw new Exception("Source and View cannot both be empty" );			
		}
	}

?>