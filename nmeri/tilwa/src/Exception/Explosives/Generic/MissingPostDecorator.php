<?php
	namespace Tilwa\Exception\Generic;

	use Exception;

	class MissingPostDecorator extends Exception {

		private $concrete;

		public function __construct (string $concrete) {

			$this->concrete = $concrete;
		}

		public function getMessage ():string {

			return "Attempted to handle POST request but no decorated handler found on ". $this->concrete;
		}
	}
?>