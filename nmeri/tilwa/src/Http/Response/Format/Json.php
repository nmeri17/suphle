<?php

	namespace Tilwa\Http\Response\Format;

	class Json extends AbstractRenderer {

		function __construct(string $handler, array $middleware) {
			
			$this->middleware = $middleware;

			$this->handler = $handler;
		}

		public function render():string {

			return $this->renderJson();
		}
	}
?>