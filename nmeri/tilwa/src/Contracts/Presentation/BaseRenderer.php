<?php
	namespace Tilwa\Contracts\Presentation;

	interface BaseRenderer {

		public function render ():string;

		public function invokeActionHandler (array $handlerParameters):self;

		public function getController():string;

		public function hasBranches():bool;

		public function getHandler ():string;

		public function matchesHandler (string $name):bool {

			return $this->handler == $name;
		}

		public function setHeaders (int $statusCode, array $headers):void {

			$this->statusCode = $statusCode;

			$this->headers = array_merge($this->headers, $headers);
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

		public function getStatusCode ():int {

			return $this->statusCode;
		}

		public function getHeaders ():array {

			return $this->headers;
		}
	}
?>