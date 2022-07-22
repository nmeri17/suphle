<?php
	namespace Suphle\Exception\Explosives\Generic;

	use Exception;

	class NoCompatibleValidator extends Exception {

		public function __construct (string $controller, string $method) {

			$this->message = "Unable to find request validator for $method on $controller";
		}
	}
?>