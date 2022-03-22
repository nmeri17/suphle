<?php
	namespace Tilwa\Exception\Explosives\Generic;

	use Exception;

	class UnacceptableDependency extends Exception {

		public function __construct (string $importer, string $dependency) {

			$this->message = $importer ." is forbidden from depending on ". $dependency;
		}
	}
?>