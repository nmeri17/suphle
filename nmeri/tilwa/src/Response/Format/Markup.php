<?php
	namespace Tilwa\Response\Format;

	class Markup extends AbstractRenderer {

		protected $viewName;

		private $wantsJson, $viewModel;

		function __construct(string $handler, string $viewName, string $viewModel) {

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
	}
?>