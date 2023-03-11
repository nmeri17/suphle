<?php
	namespace Suphle\Exception\Explosives\Generic;

	use Suphle\Contracts\Exception\BroadcastableException;

	use Exception;

	class MissingPostDecorator extends Exception implements BroadcastableException {

		public function __construct (string $concrete) {

			$this->message = "Attempted to handle POST request but no decorated handler found on ". $concrete;
		}
	}
?>