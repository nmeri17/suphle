<?php

	namespace Tilwa\Controllers\Structures;

	use ErrorException;

	class ServiceEventPayload {

		private $data, $method, $error;

		public function __construct($data, string $method) {

			$this->data = $data;

			$this->method = $method;
		}

		public function getData() {
			
			return $this->data;
		}

		public function getMethod():string {
			
			return $this->method;
		}

		public function setErrors(ErrorException $error):void {
			
			$this->error = $error;
		}

		public function getErrors():ErrorException {
			
			return $this->error;
		}
	}
?>