<?php
	namespace Tilwa\Contracts\IO\Image;

	interface ImageThumbnailContract {
		
		public function setupClient ():void;

		public function setDimensions (int $width, int $height):void;

		/**
		 * @param {newPath} Can be omitted if that value is to be generated by the underlying client
		 * 
		 * * @return Location of saved file
		*/
		public function miniature (string $oldPath, ?string $newPath):string;
	}
?>