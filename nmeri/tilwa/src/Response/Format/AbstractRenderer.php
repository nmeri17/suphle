<?php
	namespace Tilwa\Response\Format;

	use Tilwa\{Contracts\HtmlParser, Flows\ControllerFlows};

	abstract class AbstractRenderer {

		private $controller, $rawResponse, $path, $flows, $routeMethod;

		protected $handler, $statusCode, $headers = [];

		public function setDependencies(HtmlParser $htmlParser, string $controllerClass):self {

			$this->htmlParser = $htmlParser;
			
			$this->controller = $controllerClass;

			return $this;
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

		abstract public function render ():string;

		public function hasBranches():bool {
			
			return !is_null($this->flows);
		}

		public function setRawResponse($response):self {
			
			$this->rawResponse = $response;

			return $this;
		}

		public function setFlow(ControllerFlows $flow):self {
			
			$this->flows = $flow;
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

			$this->headers += $headers;
		}

		public function getStatusCode ():int {

			return $this->statusCode;
		}

		public function getHeaders ():array {

			return $this->headers;
		}
	}
?>