<?php
	namespace Suphle\Request;

	use Suphle\Contracts\Requests\StdInputReader;

	class NativeInputReader implements StdInputReader {

		public function getPayload ():array {

			return json_decode(file_get_contents("php://input"), true, 512, JSON_THROW_ON_ERROR);
		}

		public function getHeaders ():array {

			return getallheaders();
		}
	}
?>