<?php
	namespace Tilwa\Services;

	use Tilwa\Services\Structures\OptionalDTO;

	use Throwable;

	abstract class InterceptsExternalPayload {

		public function getDomainObject ():OptionalDTO {

			try {
				
				return $this->translate();
			}
			catch (Throwable $e) {
				
				return $this->translationFailure();
			}
		}

		/**
		 * @param {exception} This, coupled with contents of payload may guide us on what to set on OptionalDTO
		*/
		abstract protected function translationFailure (Throwable $exception):OptionalDTO;

		/**
		 * @throws Throwable, when it meets an unexpected/undesirable payload
		*/
		abstract protected function translate ():OptionalDTO;
	}
?>