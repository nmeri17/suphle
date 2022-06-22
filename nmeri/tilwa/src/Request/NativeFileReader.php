<?php
	namespace Tilwa\Request;

	use Tilwa\Contracts\Requests\FileInputReader;

	use Laminas\Diactoros\ServerRequestFactory;

	class NativeFileReader implements FileInputReader {

		public function getFileObjects ():array {

			return ServerRequestFactory::fromGlobals()->getUploadedFiles();
		}
	}
?>