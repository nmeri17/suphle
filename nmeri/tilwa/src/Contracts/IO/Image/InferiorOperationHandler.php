<?php
	namespace Tilwa\Contracts\IO\Image;

	interface InferiorOperationHandler extends ImageOptimiseOperation {

		public function setMaxSize (int $size):void;
	}
?>