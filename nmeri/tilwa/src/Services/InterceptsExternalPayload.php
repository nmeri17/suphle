<?php
	namespace Tilwa\Services;

	use Throwable;

	abstract class InterceptsExternalPayload {

		protected $didHaveErrors = false;

		/**
		 * @return Nullable
		*/
		public function getDomainObject () {

			try {
				
				return $this->convertToDTO();
			}
			catch (Throwable $exception) {
				
				return $this->translationFailure($exception);
			}
		}

		public function hasErrors ():bool {

			return $this->didHaveErrors;
		}

		protected function translationFailure (Throwable $exception):void {

			$this->didHaveErrors = true;
		}

		/**
		 * @throws Throwable, when it meets an unexpected/undesirable payload
		*/
		abstract protected function convertToDTO ();
	}
?>