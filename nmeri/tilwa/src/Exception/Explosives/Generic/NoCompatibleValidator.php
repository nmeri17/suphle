<?php
	namespace Tilwa\Exception\Generic;

	use Exception;

	class NoCompatibleValidator extends Exception {

		private $controller, $method;

		public function __construct (string $controller, string $method) {

			$this->controller = $controller;

			$this->method = $method;
		}

		public function getMessage ():string {

			return "Unable to find request validator for {$this->method} on {$this->controller}";
		}
	}
?>