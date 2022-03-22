<?php
	namespace Tilwa\Services\Structures;

	class OptionalDTO {

		private $value, $successful;

		public function __construct ($value, bool $successful = true) {

			$this->value = $value;

			$this->successful = $successful;
		}

		public function operationValue () {

			return $this->value;
		}

		public function hasErrors ():bool {

			return !$this->successful;
		}
	}
?>