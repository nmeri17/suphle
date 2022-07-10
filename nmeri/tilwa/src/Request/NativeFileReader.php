<?php
	namespace Tilwa\Request;

	use Tilwa\Contracts\Requests\FileInputReader;

	use Symfony\Component\HttpFoundation\{Request, File\UploadedFile};

	class NativeFileReader implements FileInputReader {

		/**
		 * @return UploadedFile, Ensure that this matches what we create within file-upload tests. @see \Tilwa\Testing\Condiments\FilesystemCleaner
		*/
		public function getFileObjects ():array {

			return Request::createFromGlobals()->files->all();
		}
	}
?>