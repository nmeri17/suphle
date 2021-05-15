<?php

	namespace Tilwa\Controllers\Structures;

	class ReboundPayload {

		private $result, $method;

		public function __construct($result, string $method) {

			$this->result = $result;

			$this->method = $method;
		}

		public function getResult() {
			
			return $this->result;
		}

		public function getmethod():string {
			
			return $this->method;
		}
	}
?>