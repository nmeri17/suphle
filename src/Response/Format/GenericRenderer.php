<?php
	namespace Suphle\Response\Format;

	use Suphle\Contracts\Presentation\{HtmlParser, BaseRenderer};

	use Suphle\Contracts\Services\Decorators\VariableDependencies;

	use Suphle\Hydration\Container;

	use Suphle\Flows\ControllerFlows;

	use Suphle\Services\ServiceCoordinator;

	abstract class GenericRenderer implements BaseRenderer, VariableDependencies {

		private $controller;
  private $path;
  private $flows;
  private $routeMethod;

		protected $handler;
  protected $statusCode;
  protected $htmlParser;
  protected $rawResponse = [];
  protected $headers = [];

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

			return json_encode($this->rawResponse, JSON_THROW_ON_ERROR);
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

		public function getRawResponse():iterable {
			
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