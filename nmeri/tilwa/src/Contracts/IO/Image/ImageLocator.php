<?php
	namespace Tilwa\Contracts\IO\Image;

	use Psr\Http\Message\UploadedFileInterface;

	interface ImageLocator {

		/**
		 * No actual movement is required
		 * 
		 * @param {operationName} thumbnail|inferior
		 * @param {resourceName} e.g. profile_photo
		 * 
		 * @return string|null when name is to be generated by upload client
		*/
		public function resolveName (UploadedFileInterface $file, string $operationName, string $resourceName):string;

		/**
		 * Should move the image to a new location
		 * 
		 * @return path image was moved to
		*/
		public function temporarilyRelocate (UploadedFileInterface $image):string;
	}
?>