<?php

	namespace Tilwa\Http\Response\Format;

	class Reload extends AbstractRenderer {

		function __construct(string $handler) {

			$this->handler = $handler;
		}

		public function render() {

			$this->rawResponse += $this->router->getPrevious()->rawResponse; // avoid overwriting our own response
			
			return $this->renderHtml();
		}
	}
?>