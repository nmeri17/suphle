<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Auth\{Renderers\ApiLoginRenderer, Repositories\ApiAuthRepo};

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	class ApiLoginRendererTest extends IsolatedComponentTest {

		use TestLoginRenderer;

		const LOGIN_PATH = "/api/v1/login";

		protected function loginRendererName ():string {

			return ApiLoginRenderer::class;
		}

		protected function loginRepoService ():string {

			return ApiAuthRepo::class;
		}

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