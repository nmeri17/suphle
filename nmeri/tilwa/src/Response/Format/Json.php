<?php

	namespace Tilwa\Response\Format;

	class Json extends AbstractRenderer {

		function __construct(string $handler) {

			$this->handler = $handler;
		}

		public function render():string {

			return $this->renderJson();
		}
	}
?>