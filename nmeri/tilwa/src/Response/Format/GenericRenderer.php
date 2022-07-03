<?php
	namespace Tilwa\Response\Format;

	use Tilwa\Contracts\Presentation\{HtmlParser, BaseRenderer};

	use Tilwa\Contracts\Services\Decorators\VariableDependencies;

	use Tilwa\Hydration\Container;

	use Tilwa\Flows\ControllerFlows;

	use Tilwa\Services\ServiceCoordinator;

	abstract class GenericRenderer implements BaseRenderer, VariableDependencies {

		private $controller, $path, $flows, $routeMethod;

		protected $handler, $statusCode, $htmlParser,

		$rawResponse = [], $headers = [];

		public function setControllingClass (ServiceCoordinator $controller):void {
			
			$this->controller = $controller;
		}

		public function dependencyMethods ():array {

			return [ "setHtmlParser"];
		}

		public function setHtmlParser (HtmlParser $parser):void {

			$this->htmlParser = $parser;
		}

		public function invokeActionHandler (array $handlerParameters):BaseRenderer {

			$this->rawResponse = call_user_func_array(

				[$this->getController(), $this->handler],

				$handlerParameters
			);

			return $this;
		}

		public function getController ():ServiceCoordinator {
			
			return $this->controller;
		}

		protected function renderJson():string {

			return json_encode($this->rawResponse);
		}

		protected function renderHtml(...$arguments):string {
			
			return $this->htmlParser->parseAll(...$arguments);
		}

		public function hasBranches():bool {
			
			return !is_null($this->getFlow());
		}

		public function setRawResponse($response):BaseRenderer {
			
			$this->rawResponse = $response;

			return $this;
		}

		public function setFlow(ControllerFlows $flow):BaseRenderer {
			
			$this->flows = $flow;

			return $this;
		}

		public function getFlow():?ControllerFlows {
			
			return $this->flows;
		}

		public function getRawResponse() {
			
			return $this->rawResponse;
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
	}
?>