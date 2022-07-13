<?php
	namespace Tilwa\Testing\Proxies\Extensions;

	use Tilwa\Contracts\IO\Session as SessionContract;

	use Illuminate\{Testing\TestResponse, Http\Response};

	class TestResponseBridge extends TestResponse {

		private $sessionClient;

		public function __construct (Response $response, SessionContract $sessionClient) {

			$this->sessionClient = $sessionClient;

			parent::__construct($response);
		}

		protected function session () {

			return $this->sessionClient;
		}
	}
?>