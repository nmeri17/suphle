<?php

	namespace Tilwa\Routing;

	use SuperClosure\Serializer;

	use Tilwa\Http\Request\BaseRequest;

	class Route {

		public $pattern;

		public $placeholderMap;

		public $method;

		private $middleware; // array

		public $requestSlug;

		private $rawResponse;

		private $handler;

		private $request;

		private $controller;


		function __construct( string $pathPattern, $handler,

			string $method = "get"
		) {

			$this->assignMethod($method);

			$this->pattern = !strlen($pathPattern) ? 'index' : $pathPattern;
		}

		public function getMiddlewares () {

			return $this->middleware;
		}

		public function setPath (string $name):static {

			$this->requestSlug = $name;

			return $this;
		}

		public function equals (Route $route, bool $matchMethod =false) {

			$slug = preg_quote($this->requestSlug);

			$leadingSlash = '/'. preg_replace('/^\//', '\/?', $slug). '/i';

			$matchPath = preg_match($leadingSlash, $route->requestSlug);

			return $matchPath && ($matchMethod ? $this->method == $route->method : true);
		}

		public function getRequest():BaseRequest {
			
			return $this->request;
		}

		public function setRequest(BaseRequest $request):static {
			
			$this->request = $request;

			return $this;
		}

		public function renderResponse () {

			return $this->publishJson();
		}

		public function publishHtml () {

			return Bootstrap::driver("templating")->parseAll(); // facade. refactor
		}

		public function publishJson() {
			
			return json_encode($this->rawResponse);
		}

		public function execute (array $handlerParameters):static {

			$this->rawResponse = call_user_func_array([

				$this->getController(), $this->handler], $handlerParameters
			);

			return $this;
		}

		public function assignMethod($userMethod) {
			
			$methods = ["get", "post", "put", "delete"];

			$this->method = array_filter($methods, function ($m) use ($userMethod) {
				
				return $m == strtolower($userMethod);
			})[0];
		}

		public function setMiddleware($middleware ) {
			
			$this->middleware = is_string($middleware) ? [$middleware] : $middleware;
		}

		public function setController($class ) {
			
			$this->controller = $class;
		}
	}
?>