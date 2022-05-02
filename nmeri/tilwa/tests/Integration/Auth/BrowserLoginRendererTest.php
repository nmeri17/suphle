<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Auth\{ Renderers\BrowserLoginRenderer, Repositories\BrowserAuthRepo};

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	class BrowserLoginRendererTest extends IsolatedComponentTest {

		use TestLoginRenderer;

		const LOGIN_PATH = "/login";

		protected function loginRendererName ():string {

			return BrowserLoginRenderer::class;
		}

		protected function loginRepoService ():string {

			return BrowserAuthRepo::class;
		}

		public function test_successLogin () {

			// send request first (update payloadStorage) before injecting things
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