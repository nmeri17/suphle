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

		public $requestSlug;

		public $restorePrevVars;

		public $redirectTo; // mixed => callable|string

		const RELOAD = 10;

		const GET = 1;

		const POST = 2;


		/**
		* @param {viewName} setting this to false skips the trip to parse, while setting it to null assigns the name of your source handler to it
		*/
		function __construct(

			string $pathPattern, ?string $source, $viewName = null,

			?int $method = 1, $middleware = [],

			$redirectTo = null, ?bool $appendHeader = true
		) {

			$this->validateSource($source, !is_null($viewName));

			$this->assignView($viewName);

			$this->handleRedirects($redirectTo);


			$this->middleware = is_string($middleware) ? [$middleware] : $middleware;

			$this->appendHeader = $appendHeader;

			$this->pattern = !strlen($pathPattern) ? 'index' : $pathPattern;

			$this->method = $method;
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

		public function setPath (string $name):Route {

			$this->requestSlug = $name;

			return $this;
		}

		public function handleRedirects($destination) {

			if ($destination === self::RELOAD ) $this->restorePrevVars = true;

			else if (is_callable($destination)) $this->redirectTo = $destination; // will be passed data from the associated Source to build the new url
		}

		public function equals (Route $route, bool $matchMethod =false) {

			$matchPath = $this->requestSlug == $route->requestSlug;
			
			return $matchPath && ($matchMethod ? $this->method == $route->method : true);
		}
	}

?>