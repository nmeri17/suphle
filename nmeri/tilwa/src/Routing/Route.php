<?php

	namespace Tilwa\Routing;

	use SuperClosure\Serializer;

	use Exception;

	class Route {

		public $pattern;

		public $parameters;

		public $method;

		private $middleware; // array

		public $requestSlug;

		public $restorePrevPage;

		private $rawResponse;

		private $handler;


		function __construct(

			string $pathPattern, ?string $handler,

			?string $method = "get"
		) {

			$this->validateHandler($handler, !is_null($viewName))

			->setHandler()

			->assignMethod($method);


			$this->pattern = !strlen($pathPattern) ? 'index' : $pathPattern;
		}

		private function validateHandler ( $src, bool $hasView ) {

			$isDatalessView = is_null($src) && $hasView;

			if (!is_null($src) && !$isDatalessView) {

				if ( preg_match('/([\w\\\\]+@\w+)/', $src ) ) $this->handler = $src;

				else throw new Exception("Invalid handler pattern given" );
			}

			return $this;
		}

		public function getMiddlewares () {

			return $this->middleware;
		}

		public function setPath (string $name):Route {

			$this->requestSlug = $name;

			return $this;
		}

		public function equals (Route $route, bool $matchMethod =false) {

			$slug = preg_quote($this->requestSlug);

			$leadingSlash = '/'. preg_replace('/^\//', '\/?', $slug). '/i';

			$matchPath = preg_match($leadingSlash, $route->requestSlug);

			return $matchPath && ($matchMethod ? $this->method == $route->method : true);
		}

		public function getRequest() {
			
			# returns request associated with this route's action or default if none found
		}

		public function renderResponse () {

			return json_encode($this->rawResponse);
		}

		public function setRawResponse ($data) {
			
			$this->rawResponse = $data;
		}

		// sets that property to a closure that when called, passes in appropriate arguments to the action handler
		// if this route has no handler, your closure is to return an empty array
		public function setHandler () { // NOTE: THIS METHOD IS IN NEED OF REVIEW

		    $request = $this->app->router->getActiveRoute()->getRequest();

			[$class, $method ]= explode('@', $currentRoute->handler);

			$dataSrc = $container->getClass('\\' . $container->controllerNamespace .'\\' .$class); // this wiring should be done earlier, since we are inferring request from the action method

			$container->wireActionParameters($class, $method);
			return $this;
		}

		public function publishHtml () {

			return (new TemplateEngine( $this->app, $this->rawResponse ))->parseAll(); // review this call
		}

		public function publishJson() {
			
			return json_encode($this->rawResponse);
		}

		public function executeHandler () {

			return $this->handler();
		}

		private function assignMethod($userMethod) {
			
			$methods = ["get", "post", "put", "delete"];

			$this->method = array_filter($methods, function ($m) use ($userMethod) {
				
				return $m == $userMethod;
			})[0];
		}

		public function setMiddleware($middleware ) {
			
			$this->middleware = is_string($middleware) ? [$middleware] : $middleware;
		}
	}
?>