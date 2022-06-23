<?php
	namespace Tilwa\Contracts\IO\Image;

	use SplFileInfo;

	interface ImageOptimiseOperation {

		/**
		 * @return string[] of file names
		*/
		public function getTransformed ():array;

		/**
		 * @param {images} SplFileInfo[]
		*/
		public function setFiles (array $images):void;

		public function setResourceName (string $name):void;

		public function setName (string $name):void;

		public function savesAsync ():bool;

		public function getAsyncNames (string $operationName, string $imageResourceName):array;
	}
?>