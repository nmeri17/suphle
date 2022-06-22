<?php
	namespace Tilwa\Contracts\Requests;

	use SplFileInfo;

	interface FileInputReader {

		/**
		 * @return SplFileInfo[]
		*/
		public function getFileObjects ():array;
	}
?>