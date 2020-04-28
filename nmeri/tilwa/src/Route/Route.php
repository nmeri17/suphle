<?php

	namespace Tilwa\Route;

	use SuperClosure\Serializer;

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

		private $redirectTo; // callable

		public $model;

		const RELOAD = 10;

		const GET = 1;

		const POST = 2;


		/**
		* TODO: constructor parameter shouldn't be this long. the class should be the parent of sub-classes with their custom behavior or something (@see details in constructor body). The advantage of this is that those routes where presence of one field nullifies the other is expressed more declaratively
		* @param {viewName} setting this to false skips the trip to parse. If null, it assigns the name of your source handler to it
		*/
		function __construct(

			string $pathPattern, ?string $source, $viewName = null,

			?int $method = 1, $middleware = [],

			$redirectTo = null, ?bool $appendHeader = true,

			string $model = null
		) {

			$this->validateSource($source, !is_null($viewName)) // TODO: create subclass if this fits that type i.e. SourceRoute (remember to create CrudRoute). Use factory pattern for the constructor of these types i.e. $router->register returns base route while $router->viewRoute registers and returns its related type

			->assignView($viewName) // ViewRoute

			->handleRedirects($redirectTo); // RedirectRoute


			$this->middleware = is_string($middleware) ? [$middleware] : $middleware; // TODO: Defaults for all `Route` types

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

			else if (is_callable($destination)) {

				// liquefy it so it can be cached if needed
				$this->redirectTo = (new Serializer())->serialize($destination); // when called, it will be passed data from the associated Source to build the new url
			}

			return $this;
		}

		public function equals (Route $route, bool $matchMethod =false) {

			$slug = preg_quote($this->requestSlug);

			$leadingSlash = '/'. preg_replace('/^\//', '\/?', $slug). '/i';

			$matchPath = preg_match($leadingSlash, $route->requestSlug);

			return $matchPath && ($matchMethod ? $this->method == $route->method : true);
		}

		public function getRedirectDestination () {

			$location = $this->redirectTo;

			if (!$location) return null;

			return (new Serializer())->unserialize($location);
		}
	}

?>