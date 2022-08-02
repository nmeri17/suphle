<?php
	namespace Suphle\Contracts\Services;

	interface InterceptsExternalPayload {

		public function hasErrors ():bool;

		/**
		 * To be called from [getDomainObject]
		*/
		public function translationFailure ():void;

		/**
		 * @return Nullable
		*/
		public function getDomainObject ();
	}
?>