<?php

	namespace Tilwa\Routing;

	use SuperClosure\Serializer;

	use Tilwa\App\Bootstrap;

	use Exception;

	use Tilwa\Http\Request\BaseRequest;

	class Route {

		public $pattern;

		public $parameters;

		public $method;

		private $middleware; // array

		public $requestSlug;

		public $restorePrevPage;

		private $rawResponse;

		private $handler;

		private $handlerParameters;

		private $request;


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

		public function renderResponse () {

			return $this->publishJson();
		}

		// prepares appropriate values for plugging into action method
		public function setHandler (Bootstrap $app):void {

		    $handler = $this->handler;

		    if (!$handler) $this->noHandler();

		    if (is_string($handler)) {

		    	[$class, $method ]= explode('@', $handler);

				$this->handler = $app->getClass(
					
					$app->controllerNamespace .'\\' .$class
				)->$method;

				$this->handlerParameters = $container->wireActionParameters($this->handler, $this);

				$this->setRequest();
			}
		}

		private function setRequest() {
			# look through this.handlerParameters for an instance of request or return default if none found
			//
		}

		private function noHandler():void {

			$this->handler = function () {

				return [];
			};
			return;
		}

		public function publishHtml () {

			return (new TemplateEngine( $this->app, $this->rawResponse ))->parseAll(); // review this call
			// pull the templating driver
		}

		public function publishJson() {
			
			return json_encode($this->rawResponse);
		}

		public function executeHandler () {

			$this->rawResponse = call_user_func_array($this->handler, $this->handlerParameters);

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
	}
?>