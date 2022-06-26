<?php
	namespace Tilwa\Testing\Proxies\Extensions;

	use Tilwa\Contracts\Requests\FileInputReader;

	use Symfony\Component\HttpFoundation\File\UploadedFile;

	class InjectedUploadedFiles implements FileInputReader {

		private $fileMap;

		/**
		 * @param {fileMap} UploadedFile[]
		*/
		public function __construct (array $fileMap) {

			$this->fileMap = $fileMap;
		}

		public function getFileObjects ():array {

			return $this->fileMap;
		}
	}
?>