<?php
	namespace Tilwa\Errors;

	use Exception;

	class UnexpectedModules extends Exception {

		private $descriptors, $consumer;

		public function __construct (array $incompatible, string $module) {

			$this->descriptors = $incompatible;

			$this->consumer = $module
		}
	}
?>