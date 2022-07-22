<?php
	namespace Suphle\Response\Format;

	class Markup extends GenericRenderer {

		protected $viewName;

		private $wantsJson, $viewModel;

		function __construct(string $handler, string $viewName, string $viewModel = null) {

			$this->handler = $handler;

			$this->viewName = $viewName;

			$this->viewModel = $viewModel;

			$this->setHeaders(200, ["Content-Type" => "text/html"]);
		}

		public function render():string {
			
			if ( !$this->wantsJson)

				return $this->renderHtml($this->viewName, $this->viewModel, $this->rawResponse);

			$this->setHeaders(200, ["Content-Type" => "application/json"]);

			return $this->renderJson();
		}

		public function setWantsJson():void {
			
			$this->wantsJson = true;
		}

		public function getViewName ():string {

			return $this->viewName;
		}

		public function getViewModelName ():string {

			return $this->viewModel;
		}
	}
?>