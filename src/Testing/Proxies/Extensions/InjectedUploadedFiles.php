<?php
	namespace Suphle\Testing\Proxies\Extensions;

	use Suphle\Contracts\Requests\FileInputReader;

	use Symfony\Component\HttpFoundation\File\UploadedFile;

	class InjectedUploadedFiles implements FileInputReader {

		/**
		 * @param {fileMap} UploadedFile[]
		*/
		public function __construct(protected readonly array $fileMap) {

			//
		}

		public function getFileObjects ():array {

			return $this->fileMap;
		}
	}
?>