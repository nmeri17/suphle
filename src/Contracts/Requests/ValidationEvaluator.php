<?php
	namespace Suphle\Contracts\Requests;

	use Suphle\Contracts\Presentation\BaseRenderer;

	interface ValidationEvaluator {

		public function getValidatorErrors ():array;

		public function validationRenderer ():BaseRenderer;
	}
?>