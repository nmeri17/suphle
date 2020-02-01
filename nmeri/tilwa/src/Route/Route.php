<?php

	namespace Tilwa\Route;

	use Exception;

	class Route {

		public $pattern;

		public $parameters;

		public $method;

		public $viewName;

		private $middleware;

		public $source;


		/**
		* @param {viewName} setting this to false skips the trip to parse, while setting it to null assigns the name of your source handler to it
		*/
		function __construct(

			string $pathPattern, ?string $source, $viewName = null,

			?string $method = 'get', ?bool $appendHeader = true, $middleware = []
		) {

			$this->validateSource($source, !is_null($viewName));

			$this->assignView($viewName);


			$this->middleware = is_string($middleware) ? [$middleware] : $middleware;

			$this->appendHeader = $appendHeader;

			$this->pattern = !strlen($pathPattern) ? 'index' : $pathPattern;

			$this->method = strtolower($method);
		}

		private function validateSource ( $src, bool $hasView ) {

			$isDatalessView = is_null($src) && $hasView;

			if ($isDatalessView) return;

			elseif (!is_null($src)) {

				if ( preg_match('/([\w\\\\]+@\w+)/', $src ) ) $this->source = $src;

				else throw new Exception("Invalid source pattern given" );
			}
		}

		public function getMiddlewares () {

			return $this->middleware;
		}

		private function assignView ( $name ) {

			if (!is_null($name) || $name === false)

				$this->viewName = $name;

			elseif ( $source = $this->source )

				$this->viewName = explode('@', $source)[1]; // if no view is supplied, we assume view name matches source method

			else throw new Exception("Source and View cannot both be empty" ); // likely null			
		}
	}

?>