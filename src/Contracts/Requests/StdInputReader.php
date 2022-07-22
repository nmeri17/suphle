<?php
	namespace Suphle\Contracts\Requests;

	interface StdInputReader {

		public function getPayload ():array;

		public function getHeaders ():array;
	}
?>