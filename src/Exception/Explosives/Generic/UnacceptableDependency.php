<?php
	namespace Suphle\Exception\Explosives\Generic;

	use Suphle\Contracts\Exception\BroadcastableException;

	use Exception;

	class UnacceptableDependency extends Exception implements BroadcastableException {

		public function __construct (string $importer, string $dependency) {

			$this->message = $importer ." is forbidden from depending on ". $dependency;
		}
	}
?>