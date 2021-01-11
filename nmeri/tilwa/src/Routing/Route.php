<?php

	namespace Tilwa\Routing;

	use SuperClosure\Serializer;

	use Tilwa\Http\Request\BaseRequest;

	class Route {

		public $pattern;

		public $method;

		private $middleware; // so, how do we access this guy given the route itself is now cutoff?

		public $requestSlug;

		private $request;

		function __construct(string $pattern, string $method) {

			$this->method = $method;

			$this->pattern = $pattern;
		}

		public function getMiddlewares () {

			return $this->middleware;
		}

		public function setPath (string $name):self {

			$this->requestSlug = $name;

			return $this;
		}

		public function getRequest():BaseRequest {
			
			return $this->request;
		}

		public function setRequest(BaseRequest $request):static {
			
			$this->request = $request;

			return $this;
		}

		public function assignMethod($userMethod):self {
			
			$methods = ["get", "post", "put", "delete"];

			$this->method = array_filter($methods, function ($m) use ($userMethod) {
				
				return $m == strtolower($userMethod);
			})[0];

			return $this;
		}

		public function setMiddleware(array $middleware ) {
			
			$this->middleware = $middleware;
		}
	}
?>