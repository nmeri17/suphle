<?php
	namespace Tilwa\Exception\Generic;

	use Exception;

	class InvalidImplementor extends Exception {

		private $interface, $concrete;

		public function __construct (string $interface, string $concrete) {

			$this->interface = $interface;

			$this->concrete = $concrete;
		}

		public function getMessage ():string {

			return $this->concrete ." incorrectly provided for ". $this->interface;
		}
	}
?>