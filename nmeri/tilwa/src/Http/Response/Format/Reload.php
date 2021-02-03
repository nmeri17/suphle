<?php

	namespace Tilwa\Http\Response\Format;

	class Reload extends AbstractRenderer {

		// change to 50* on validation error
		function __construct(string $handler, int $statusCode = 200) {
			
			$this->statusCode = $statusCode;

			$this->handler = $handler;
		}

		public function render() {

			$this->rawResponse += $this->router->getPrevious()->rawResponse; // avoid overwriting our own response
			
			return $this->renderHtml();
		}
	}
?>