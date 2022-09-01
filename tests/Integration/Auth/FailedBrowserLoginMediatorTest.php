<?php
	namespace Suphle\Tests\Integration\Auth;

	use Suphle\Tests\Integration\Auth\Bases\BaseTestBrowserLoginMediator;

	class FailedBrowserLoginMediatorTest extends BaseTestBrowserLoginMediator {

		protected function preDatabaseFreeze ():void {

			$this->sendIncorrectRequest(self::LOGIN_PATH); // given
		}

		public function test_failedLogin () {

			$this->injectLoginMediator(0, 1); // then

			$this->evaluateLoginStatus(); // when
		}
	}
?>