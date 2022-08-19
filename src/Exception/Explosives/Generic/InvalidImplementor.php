<?php
	namespace Suphle\Exception\Explosives\Generic;

	use Suphle\Contracts\Exception\BroadcastableException;

	use Exception;

	class InvalidImplementor extends Exception implements BroadcastableException {

		public function __construct (string $interface, string $concrete) {

			$this->message = $concrete ." incorrectly provided for ". $interface; // unfortunately, getMessage is final, so we're resorting to this hack
		}
	}
?>