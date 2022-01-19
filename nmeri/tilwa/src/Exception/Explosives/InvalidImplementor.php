<?php
	namespace Tilwa\Errors;

	use Exception;

	class InvalidImplementor extends Exception {

		private $interface, $concrete;

		public function __construct (string $interface, string $concrete) {

			$this->interface = $interface;

			$this->concrete = $concrete;
		}
	}
?>