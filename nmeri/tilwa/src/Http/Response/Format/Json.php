<?php

	namespace Tilwa\Http\Response\Format;

	class Json extends AbstractRenderer {

		function __construct(string $handler, int $statusCode = 200) {
			
			$this->statusCode = $statusCode;

			$this->handler = $handler;
		}

		public function render():string {

			return $this->renderJson();
		}
	}
?>