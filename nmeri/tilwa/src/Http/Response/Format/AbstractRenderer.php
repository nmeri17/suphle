<?php

	namespace Tilwa\Http\Response\Format;

	use Tilwa\Routing\RouteManager;

	use Tilwa\Contracts\HtmlParser;

	abstract class AbstractRenderer {

		public $router;

		protected $handler;

		private $controller;

		private $rawResponse;

		protected $module;

		public function setRouter(RouteManager $router):void {

			$this->router = $router;
		}

		public function getRouter():RouteManager {

			return $this->router;
		}

		public function execute (array $handlerParameters):static {

			$this->rawResponse = call_user_func_array([

				$this->controller, $this->handler], $handlerParameters
			);

			return $this;
		}

		public function setController($class ):self {
			
			$this->controller = $class;

			return $this;
		}

		public function setModule(Bootstrap $module):void {

			$this->module = $module;
		}

		protected function renderJson():string {

			$route = $this->router->getActiveRoute();

			$request = $route->getRequest();

			if (!$request->isValidated())

				$response = $request->validationErrors();

			else $response = $this->rawResponse;
			
			return json_encode($response);
		}

		protected function renderHtml() {
			
			return $this->module->getClass(HtmlParser::class)

			->parseAll($this->viewName, $this->rawResponse);
		}

		abstract public function render ():string;
	}
?>