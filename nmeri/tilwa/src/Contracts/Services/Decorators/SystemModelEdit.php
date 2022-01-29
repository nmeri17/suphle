<?php
	namespace Tilwa\Contracts\Services\Decorators;

	interface SystemModelEdit extends ServiceErrorCatcher {

		public function updateModels ();

		/**
		 * Use in [updateModels]
		*/
		public function modelsToUpdate ():array;
	}
?>