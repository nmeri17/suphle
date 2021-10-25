<?php
	namespace Tilwa\Contracts\Requests;

	interface RequestValidator {

		protected function validate ():void;

		public function getErrors ():array;

		public function setErrors ():void;
	}
?>