<?php

	namespace Nmeri\Tilwa\Route;

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

			string $method = 'get', bool $appendHeader = true, $middleware = []
		) {

			$this->hasQuery();

			$this->validateSource($source);

			$this->assignView($viewName);


			$this->middleware = is_string($middleware) ? [$middleware] : $middleware;

			$this->appendHeader = $appendHeader;

			$this->pattern = $pathPattern;

			$this->method = strtolower($method);
		}

		private function hasQuery () {
			
			preg_match('/([\w=&,-:]+)$/', @urldecode($_GET['query']), $viewState);

			if (!empty($viewState)) $this->queryVars = $viewState[1];
		}

		private function validateSource ( $src ) {

			// 1st confirm a source was given to begin with. If none, assume path doesn't include dynamic vars
			if ( !is_null($src) ) {

				if ( preg_match('/([\w\\]+@\w+)/', $src, $res) ) $this->source = $src;

				throw new Exception("Invalid source pattern given" );
			}
		}

		public function getMiddlewares () {

			return $this->middleware;
		}

		private function assignView ( $name ) {

			if (!is_null($name)) $this->viewName = $name;

			elseif ( $source = $this->source ) $this->viewName = explode('@', $source)[1];

			throw new Exception("Source and View cannot both be empty" );			
		}
	}

?>