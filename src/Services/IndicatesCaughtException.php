<?php
	namespace Suphle\Services;

	use Suphle\Contracts\Services\InterceptsExternalPayload;

	abstract class IndicatesCaughtException implements InterceptsExternalPayload {

		protected $didHaveErrors = false;

		public function hasErrors ():bool {

			return $this->didHaveErrors;
		}

		public function translationFailure ():void {

			$this->didHaveErrors = true;
		}
	}
?>