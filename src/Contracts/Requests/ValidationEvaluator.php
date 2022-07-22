<?php
	namespace Suphle\Contracts\Requests;

	interface ValidationEvaluator {

		public function getValidatorErrors ():array;
	}
?>