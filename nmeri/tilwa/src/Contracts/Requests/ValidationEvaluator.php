<?php
	namespace Tilwa\Contracts\Requests;

	interface ValidationEvaluator {

		protected function getValidatorErrors ():array;
	}
?>