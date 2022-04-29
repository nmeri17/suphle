<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Auth\{Renderers\ApiLoginRenderer, Repositories\ApiAuthRepo};

	class ApiLoginRendererTest extends TestLoginRenderer {

		const LOGIN_PATH = "/api/v1/login";

		protected $loginRendererName = ApiLoginRenderer::class,

		$loginRepoService = ApiAuthRepo::class;

		public function test_successLogin () {

			$this->sendCorrectRequest(self::LOGIN_PATH); // given

			$this->injectLoginRenderer(1, 0); // then

			$this->evaluateLoginStatus(); // when
		}

		public function test_failedLogin () {

			$this->sendIncorrectRequest(self::LOGIN_PATH); // given

			$this->injectLoginRenderer(0, 1); // then

			$this->evaluateLoginStatus(); // when
		}
	}
?>