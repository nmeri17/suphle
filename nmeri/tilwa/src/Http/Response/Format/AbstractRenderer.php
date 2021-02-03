<?php

	namespace Tilwa\Http\Response\Format;

	use Tilwa\Routing\RouteManager;

	use Tilwa\Contracts\HtmlParser;

	use Tilwa\Http\Request\BaseRequest;

	abstract class AbstractRenderer {

		protected $router;

		public $handler;

		private $controller;

		private $rawResponse;

		protected $module;

		public $routeMethod;

		private $request;

		protected $statusCode;

		public $path;

		public function setDependencies(RouteManager $router, Bootstrap $module, string $controllerClass):self {

			$this->router = $router;

			$this->module = $module;
			
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
			
			return $this->module->getClass(HtmlParser::class)

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