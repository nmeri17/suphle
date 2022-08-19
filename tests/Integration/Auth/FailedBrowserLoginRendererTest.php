<?php
	namespace Suphle\Tests\Integration\Auth;

	use Suphle\Tests\Integration\Auth\Bases\BaseTestBrowserLoginRenderer;

	class FailedBrowserLoginRendererTest extends BaseTestBrowserLoginRenderer {

		protected function preDatabaseFreeze ():void {

			$this->sendIncorrectRequest(self::LOGIN_PATH); // given
		}

		public function test_failedLogin () {

			$this->injectLoginRenderer(0, 1); // then

			$this->evaluateLoginStatus(); // when
		}
	}
?>