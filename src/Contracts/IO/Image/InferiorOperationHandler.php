<?php
	namespace Suphle\Contracts\IO\Image;

	interface InferiorOperationHandler extends ImageOptimiseOperation {

		const OPERATION_NAME = "inferior";

		public function setMaxSize (int $size):void;
	}
?>