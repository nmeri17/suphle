<?php
	namespace Tilwa\Exception\Generic;

	use Exception;

	class UnacceptableDependency extends Exception {

		private $importer, $dependency;

		public function __construct (string $importer, string $dependency) {

			$this->importer = $importer;

			$this->dependency = $dependency;
		}

		public function getMessage ():string {

			return $this->importer ." is forbidden from depending on ". $this->dependency;
		}
	}
?>