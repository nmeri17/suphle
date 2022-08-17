<?php
	namespace Suphle\Exception\Explosives\Generic;

	use Suphle\Contracts\Exception\BroadcastableException;

	use Exception;

	class UnexpectedModules extends Exception implements BroadcastableException {

		public function __construct (array $incompatible, string $module) {

			$this->message = "Invalid descriptors given to module $module " . json_encode($incompatible);
		}
	}
?>