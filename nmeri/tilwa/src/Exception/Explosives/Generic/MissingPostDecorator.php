<?php
	namespace Tilwa\Exception\Explosives\Generic;

	use Exception;

	class MissingPostDecorator extends Exception {

		public function __construct (string $concrete) {

			$this->message = "Attempted to handle POST request but no decorated handler found on ". $concrete;
		}
	}
?>