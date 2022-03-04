<?php
	namespace Tilwa\Contracts\IO;

	use Psr\Http\Message\UploadedFileInterface;

	interface ImageOptimiseOperation {

		/**
		 * @return string[] of file names
		*/
		public function getTransformed (string $operationName, string $imageResourceName):array;

		/**
		 * @param {images} UploadedFileInterface[]
		*/
		public function setFiles (array $images):void;

		public function setResourceName (string $name):void;

		public function setName (string $name):void;

		public function savesAsync ():bool;

		public function getAsyncNames (string $operationName, string $imageResourceName):array
	}
?>