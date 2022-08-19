<?php
	namespace Suphle\Response\Format;

	class Json extends GenericRenderer {

		function __construct(string $handler) {

			$this->handler = $handler;

			$this->setHeaders(200, ["Content-Type" => "application/json"]);
		}

		public function render():string {

			return $this->renderJson();
		}
	}
?>