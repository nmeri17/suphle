<?php

	namespace Tilwa\Http\Response\Format;

	use Tilwa\Contracts\HtmlParser;

	use Tilwa\Http\Request\BaseRequest;

	abstract class AbstractRenderer {

		public $handler;

		private $controller;

		private $rawResponse;

		protected $container;

		public $routeMethod;

		private $request;

		protected $statusCode;

		public $path;

		public function setDependencies(Container $container, string $controllerClass):self {

			$this->container = $container;
			
			$this->controller = $controllerClass;

			return $this;
		}

		public function execute (array $handlerParameters):self {

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
	}
?>