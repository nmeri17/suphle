<?php
	namespace Suphle\Contracts\IO\Image;

	interface ImageThumbnailClient {
		
		public function setupClient ():void;

		public function setDimensions (int $width, int $height):void;

		/**
		 * * @return Location of saved file
		*/
		public function miniature (string $imageNewPath):string;
	}
?>