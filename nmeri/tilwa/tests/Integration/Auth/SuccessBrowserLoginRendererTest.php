<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Tests\Integration\Auth\Bases\BaseTestBrowserLoginRenderer;

	class SuccessBrowserLoginRendererTest extends BaseTestBrowserLoginRenderer {

		protected function preDatabaseFreeze ():void {

			$this->sendCorrectRequest(self::LOGIN_PATH); // given
		}

		public function test_successLogin () {

			$this->injectLoginRenderer(1, 0); // then

			$this->evaluateLoginStatus(); // when
		}
	}
?>