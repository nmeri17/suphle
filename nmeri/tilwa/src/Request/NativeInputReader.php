<?php
	namespace Tilwa\Request;

	use Tilwa\Contracts\Requests\StdInputReader;

	class NativeInputReader implements StdInputReader {

		public function getPayload ():array {

			return json_decode(file_get_contents("php://input"), true);
		}

		public function getHeaders ():array {

			return getallheaders();
		}
	}
?>