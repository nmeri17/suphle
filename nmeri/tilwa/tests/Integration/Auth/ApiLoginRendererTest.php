<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Auth\Renderers\ApiLoginRenderer;

	class ApiLoginRendererTest extends TestLoginRenderer {

		private $loginPath = "/api/v1/login";

		protected $loginRendererName = ApiLoginRenderer::class;

		public function test_successLogin () {

			$this->injectLoginRenderer(1, 0); // then

			$this->sendCorrectRequest($this->loginPath); // given

			$this->getLoginResponse(); // when
		}

		public function test_failedLogin () {

			$this->injectLoginRenderer(0, 1); // then

			$this->sendIncorrectRequest($this->loginPath); // given

			$response = $this->getLoginResponse(); // when
		}
	}
?>