<?php
	namespace Tilwa\Contracts;

	interface RequestValidator {

		private $validator;

		private $errorHolder;

		protected function validate ():void;

		public function getErrors ():array;

		public function setErrors ():void;
	}
?>