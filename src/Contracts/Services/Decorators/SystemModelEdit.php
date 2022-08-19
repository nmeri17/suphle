<?php
	namespace Suphle\Contracts\Services\Decorators;

	interface SystemModelEdit extends ServiceErrorCatcher {

		/**
		 * @return Mixed. Perhaps, result of the update to any interested caller
		*/
		public function updateModels ();

		/**
		 * Foundation from which [modelsToUpdate] and [updateModels] are expected to construct their queries. As such, it should be called before either of the two
		 * 
		 * @param {baseModel} Passed down from ModelfulPayload
		*/
		public function initializeUpdateModels ($baseModel):void;

		/**
		 * The rows to be locked while running the update. Should correspond to the rows of [updateModels]
		*/
		public function modelsToUpdate ():array;
	}
?>