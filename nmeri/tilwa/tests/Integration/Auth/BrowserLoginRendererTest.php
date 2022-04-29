<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Auth\{ Renderers\BrowserLoginRenderer, Repositories\BrowserAuthRepo};

	class BrowserLoginRendererTest extends TestLoginRenderer {

		const LOGIN_PATH = "/login";

		protected $loginRendererName = BrowserLoginRenderer::class,

		$loginRepoService = BrowserAuthRepo::class;

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