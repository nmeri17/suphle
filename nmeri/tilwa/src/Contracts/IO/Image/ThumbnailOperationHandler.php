<?php
	namespace Tilwa\Contracts\IO\Image;

	interface ThumbnailOperationHandler extends ImageOptimiseOperation {

		public function setDimensions(int $width, int $height):void;
	}
?>