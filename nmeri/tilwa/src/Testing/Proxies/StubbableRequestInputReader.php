<?php
	namespace Tilwa\Testing\Proxies;

	use Tilwa\Contracts\Requests\StdInputReader;

	class StubbableRequestInputReader implements StdInputReader {

		private $payload;

		public function __construct (array $payload) {

			$this->payload = $payload;
		}

		public function getAll ():array {

			return $this->payload;
		}
	}
?>