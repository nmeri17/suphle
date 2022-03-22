<?php
	namespace Tilwa\Exception\Explosives\Generic;

	use Exception;

	class CsrfException extends Exception {

		public function __construct () {

			$this->message = "Non-GET request missing CSRF token. Consider adding hidden token field";
		}
	}
?>