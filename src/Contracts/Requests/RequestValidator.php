<?php
	namespace Suphle\Contracts\Requests;

	interface RequestValidator {

		public function validate (array $parameters, array $rules):void;

		public function getErrors ():iterable;
	}
?>