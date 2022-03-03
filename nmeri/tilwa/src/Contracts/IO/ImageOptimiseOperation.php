<?php
	namespace Tilwa\Contracts\IO;

	use Psr\Http\Message\UploadedFileInterface;

	interface ImageOptimiseOperation {

		/**
		 * @return UploadedFileInterface[]
		*/
		public function getTransformed ():array;

		/**
		 * @param {images} UploadedFileInterface[]
		*/
		public function setFiles (array $images):void;
	}
?>