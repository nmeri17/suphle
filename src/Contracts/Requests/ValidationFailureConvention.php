<?php
	namespace Suphle\Contracts\Requests;

	use Suphle\Contracts\Presentation\BaseRenderer;

	interface ValidationFailureConvention {

		public function deriveFormPartial ():BaseRenderer;
	}
?>