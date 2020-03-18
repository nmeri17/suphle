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

		public $restorePrevPage;

		public $redirectTo; // callable

		const RELOAD = 10;

		const GET = 1;

		const POST = 2;


		/**
		* @param {viewName} setting this to false skips the trip to parse. If null, it assigns the name of your source handler to it
		*/
		function __construct(

			string $pathPattern, ?string $source, $viewName = null,

			?int $method = 1, $middleware = [],

			$redirectTo = null, ?bool $appendHeader = true
		) {

			$this->validateSource($source, !is_null($viewName))

			->assignView($viewName)

			->handleRedirects($redirectTo);


			$this->middleware = is_string($middleware) ? [$middleware] : $middleware;

			$this->appendHeader = $appendHeader;

			$this->pattern = !strlen($pathPattern) ? 'index' : $pathPattern;

			$this->method = $method ?? self::GET;
		}

		private function validateSource ( $src, bool $hasView ) {

			$isDatalessView = is_null($src) && $hasView;

			if (!is_null($src) && !$isDatalessView) {

				if ( preg_match('/([\w\\\\]+@\w+)/', $src ) ) $this->source = $src;

				else throw new Exception("Invalid source pattern given" );
			}

			return $this;
		}

		public function getMiddlewares () {

			return $this->middleware;
		}

		private function assignView ( $name ) {

			if (!is_null($name) || $name === false)

				$this->viewName = $name;

			elseif ( $source = $this->source ) { // if no view is supplied, we assume view name matches kebab-case source method
				[$dir, $fileName ]= explode('@', $source);

				$this->viewName = $dir . '/'. preg_replace_callback('/([a-z]+)([A-Z])/', function($m) {

				  return $m[1] . '-' . strtolower($m[2]);
				}, $fileName);
			}

			else throw new Exception("Source and View cannot both be empty" );

			return $this;
		}

		public function setPath (string $name):Route {

			$this->requestSlug = $name;

			return $this;
		}

		public function handleRedirects($destination) {

			if ($destination === self::RELOAD ) $this->restorePrevPage = true;

			else if (is_callable($destination)) $this->redirectTo = $destination; // will be passed data from the associated Source to build the new url

			return $this;
		}

		public function equals (Route $route, bool $matchMethod =false) {

			$matchPath = $this->requestSlug == $route->requestSlug;
			
			return $matchPath && ($matchMethod ? $this->method == $route->method : true);
		}
	}

?>