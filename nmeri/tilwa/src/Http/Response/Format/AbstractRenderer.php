<?php

	namespace Tilwa\Http\Response\Format;

	use Tilwa\Contracts\{HtmlParser, Authenticator, QueueManager};

	use Tilwa\Http\Request\BaseRequest;

	use Tilwa\Flows\ControllerFlows;

	abstract class AbstractRenderer {

		public $handler;

		private $controller;

		private $rawResponse;

		protected $container;

		public $routeMethod;

		private $request;

		private $path;

		private $flows;

		private $authenticator;

		private $queueManager;

		public function setDependencies(Container $container, string $controllerClass, Authenticator $authenticator, QueueManager $queueManager):self {

			$this->container = $container;
			
			$this->controller = $controllerClass;

			$this->authenticator = $authenticator;

			$this->queueManager = $queueManager;

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
			
			return $this->container->getClass(HtmlParser::class)

			->parseAll($this->viewName, $this->rawResponse);
		}

		public function getRequest():BaseRequest {
			
			return $this->request;
		}

		public function setRequest(BaseRequest $request):self {
			
			$this->request = $request;

			return $this;
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
	}
?>