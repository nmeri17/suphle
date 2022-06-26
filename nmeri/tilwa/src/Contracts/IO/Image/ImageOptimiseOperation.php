<?php
	namespace Tilwa\Contracts\IO\Image;

	use Symfony\Component\HttpFoundation\File\UploadedFile;

	interface ImageOptimiseOperation {

		/**
		 * @return string[] of file names
		*/
		public function getTransformed ():array;

		/**
		 * @param {images} UploadedFile[]
		*/
		public function setFiles (array $images):void;

		public function setResourceName (string $name):void;

		/**
		 * @return Name of sub-folder where image will be stored e.g. images/{operationName}/{resourceName}
		*/
		public function getOperationName ():string;

		public function savesAsync ():bool;

		public function getAsyncNames ( string $imageResourceName):array;
	}
?>