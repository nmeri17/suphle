<?php

	namespace Tilwa\Http\Response\Format;

	class Markup extends AbstractRenderer {

		public $viewName;

		public $handler;

		public $contentNegotiable;

		private $wantsJson;

		function __construct(string $handler, string $viewName) {

			$this->viewName = $viewName;

			$this->handler = $handler;
		}

		public function render() {
			
			if (!$this->contentNegotiable && !$this->wantsJson())

				return $this->renderHtml();

			return $this->renderJson();
		}

		public function contentIsNegotiable():void {
			
			$this->contentNegotiable = true;
		}

		public function wantsJson():bool {
			
			return $this->wantsJson;
		}

		public function setWantsJson():void {
			
			return $this->wantsJson = true;
		}
	}
?>