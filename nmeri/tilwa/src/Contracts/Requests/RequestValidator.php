<?php
	namespace Tilwa\Contracts\Requests;

	interface RequestValidator {

		public function validate (array $parameters, array $rules):void;

		public function getErrors ():array;

		public function setErrors (array $errors):void;
	}
?>