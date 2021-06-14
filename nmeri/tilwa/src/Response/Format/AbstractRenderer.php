<?php

	namespace Tilwa\Response\Format;

	use Tilwa\Contracts\{HtmlParser, Authenticator, QueueManager};

	use Tilwa\Request\BaseRequest;

	use Tilwa\Flows\ControllerFlows;

	abstract class AbstractRenderer {

		protected $container;

		private $controller, $rawResponse, $path, $flows, $routeMethod, $handler;


		public function setDependencies(Container $container, string $controllerClass):self {

			$this->container = $container;
			
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

			$request = $this->request;

			if (!$request->isValidated())

				$response = $request->validationErrors();

			else $response = $this->rawResponse;
			
			return json_encode($response);
		}

		protected function renderHtml():string {
			
			return $this->container->getClass(HtmlParser::class) // lazily pull from container

			->parseAll($this->viewName, $this->rawResponse);
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

		public function getContainer():Container {
			
			return $this->container;
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
	}
?>