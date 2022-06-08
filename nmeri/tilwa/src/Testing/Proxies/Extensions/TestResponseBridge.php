<?php
	namespace Tilwa\Testing\Proxies\Extensions;

	use Tilwa\IO\Session\InMemorySession;

	use Illuminate\{Testing\TestResponse, Http\Response};

	class TestResponseBridge extends TestResponse {

		private $sessionClient;

		public function __construct (Response $response, InMemorySession $sessionClient) {

			$this->sessionClient = $sessionClient;

			parent::__construct($response);
		}

		protected function session () {

			return $this->sessionClient;
		}
	}
?>