<?php
	namespace Tilwa\Contracts\Requests;

	interface StdInputReader {

		public function getPayload ():array;

		public function getHeaders ():array;
	}
?>