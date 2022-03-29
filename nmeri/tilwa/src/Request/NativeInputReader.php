<?php
	namespace Tilwa\Request;

	use Tilwa\Contracts\Requests\StdInputReader;

	class NativeInputReader implements StdInputReader {

		public function getAll ():array {

			return json_decode(file_get_contents("php://input"), true);
		}
	}
?>