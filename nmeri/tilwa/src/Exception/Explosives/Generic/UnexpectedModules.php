<?php
	namespace Tilwa\Exception\Explosives\Generic;

	use Exception;

	class UnexpectedModules extends Exception {

		public function __construct (array $incompatible, string $module) {

			$this->message = "Invalid descriptors given to module $module " . json_encode($incompatible);
		}
	}
?>