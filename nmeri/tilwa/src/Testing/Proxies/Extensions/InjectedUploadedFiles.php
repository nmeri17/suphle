<?php
	namespace Tilwa\Testing\Proxies\Extensions;

	use Tilwa\Contracts\Requests\FileInputReader;

	use SplFileInfo;

	class InjectedUploadedFiles implements FileInputReader {

		private $fileMap;

		/**
		 * @param {fileMap} SplFileInfo[]
		*/
		public function __construct (array $fileMap) {

			$this->fileMap = $fileMap;
		}

		public function getFileObjects ():array {

			return $this->fileMap;
		}
	}
?>