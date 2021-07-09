<?php

	namespace Tilwa\Response\Format;

	class Markup extends AbstractRenderer {

		protected $viewName;

		private $wantsJson, $contentNegotiable, $viewModel;

		function __construct(string $handler, string $viewName, string $viewModel) {

			$this->handler = $handler;

			$this->viewName = $viewName;

			$this->viewModel = $viewModel;
		}

		public function render():string {
			
			if (!$this->contentNegotiable && !$this->wantsJson())

				return $this->renderHtml($this->viewName, $this->viewModel, $this->rawResponse);

			return $this->renderJson();
		}

		public function contentIsNegotiable():void {
			
			$this->contentNegotiable = true;
		}

		public function wantsJson():bool {
			
			return $this->wantsJson;
		}

		public function setWantsJson():void {
			
			$this->wantsJson = true;
		}
	}
?>