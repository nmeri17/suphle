<?php
	namespace Tilwa\Contracts\Requests;

	interface ValidationEvaluator {

		public function getValidatorErrors ():array;
	}
?>