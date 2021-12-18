<?php
	namespace Tilwa\Errors;

	use Exception;

	class InvalidModuleImport extends Exception {

		private $dependencyName;

		public function __construct (string $interface) {

			$this->dependencyName = $interface;
		}
	}
?>