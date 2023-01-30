<?php
	namespace Suphle\Exception\Explosives\Generic;

	use Suphle\Contracts\Exception\BroadcastableException;

	use Exception;

	class NoCompatibleValidator extends Exception implements BroadcastableException {

		public function __construct (string $coordinator, string $method) {

			$this->message = "Unable to find request validator for the method '$coordinator::$method'";
		}
	}
?>