<?php
	namespace Tilwa\Response\Format;

	use Tilwa\Contracts\Presentation\{HtmlParser, BaseRenderer};

	use Tilwa\Flows\ControllerFlows;

	class GenericRenderer implements BaseRenderer {

		private $controller, $rawResponse, $path, $flows, $routeMethod;

		protected $handler, $statusCode, $headers = [];

		public function setControllingClass (string $class):void {
			
			$this->controller = $controllerClass;
		}

		protected function getDependencies ():array {

			return [ "htmlParser" => HtmlParser::class];
		}

		public function invokeActionHandler (array $handlerParameters):self {

			$this->rawResponse = call_user_func_array([

				$this->controller, $this->handler], $handlerParameters
			);

			return $this;
		}

		public function getController():string {
			
			return $this->controller;
		}

		protected function renderJson():string {

			return json_encode($this->rawResponse);
		}

		protected function renderHtml(...$arguments):string { // should return psr responseInterface instead
			
			return $this->htmlParser->parseAll(...$arguments);
		}

		public function hasBranches():bool {
			
			return !is_null($this->flows);
		}

		public function setRawResponse($response):self {
			
			$this->rawResponse = $response;

			return $this;
		}

		public function setFlow(ControllerFlows $flow):self {
			
			$this->flows = $flow;

			return $this;
		}

		public function getFlow():ControllerFlows {
			
			return $this->flows;
		}

		public function getRawResponse() {
			
			return $this->rawResponse;
		}

		public function getPath():string {
			
			return $this->path;
		}

		public function setPath(string $path):void {
			
			$this->path = $path;
		}

		public function getRouteMethod():string {
			
			return $this->routeMethod;
		}

		public function setRouteMethod(string $httpMethod):void {
			
			$this->routeMethod = $httpMethod;
		}

		public function getHandler ():string {

			return $this->handler;
		}

		public function matchesHandler (string $name):bool {

			return $this->handler == $name;
		}

		public function setHeaders (int $statusCode, array $headers):void {

			$this->statusCode = $statusCode;

			$this->headers = array_merge($this->headers, $headers);
		}

		public function getStatusCode ():int {

			return $this->statusCode;
		}

		public function getHeaders ():array {

			return $this->headers;
		}

		public function hydrateDependencies( Container $container):void {

			$classes = array_map(function (string $type) use ($container) {

				return $container->getClass($type);
			}, $this->getDependencies());

			foreach ($classes as $property => $concrete)

				$this->$property = $concrete;
		}
	}
?>