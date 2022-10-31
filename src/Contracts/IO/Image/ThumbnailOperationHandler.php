<?php
	namespace Suphle\Contracts\IO\Image;

	interface ThumbnailOperationHandler extends ImageOptimiseOperation {

		public const OPERATION_NAME = "thumbnail";

		public function setDimensions(int $width, int $height):void;
	}
?>