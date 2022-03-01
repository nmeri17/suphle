<?php
	namespace Tilwa\Exception\Generic;

	use Exception;

	class CsrfException extends Exception {

		public function getMessage ():string {

			return "Non-GET request missing CSRF token. Consider adding hidden token field";
		}
	}
?>