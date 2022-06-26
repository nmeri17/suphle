<?php
	namespace Tilwa\Contracts\Requests;

	use Symfony\Component\HttpFoundation\File\UploadedFile;

	interface FileInputReader {

		/**
		 * @return UploadedFile[]
		*/
		public function getFileObjects ():array;
	}
?>