<?php
	namespace Tilwa\Request;

	use Tilwa\Contracts\Requests\FileInputReader;

	use Laminas\Diactoros\ServerRequestFactory;

	use Symfony\Component\HttpFoundation\File\UploadedFile;

	class NativeFileReader implements FileInputReader {

		/**
		 * @return UploadedFile, Ensure that this matches what we create within file-upload tests
		*/
		public function getFileObjects ():array {

			return ServerRequestFactory::fromGlobals()->getUploadedFiles();
		}
	}
?>