<?php
	namespace Suphle\Tests\Integration\Auth;

	use Suphle\Tests\Integration\Auth\Bases\BaseTestApiLoginMediator;

	class FailedApiLoginMediatorTest extends BaseTestApiLoginMediator {

		protected function preDatabaseFreeze ():void {

			$this->sendIncorrectRequest(self::LOGIN_PATH); // given
		}

		public function test_failedLogin () {

			$this->injectLoginMediator(0, 1); // then

			$this->evaluateLoginStatus(); // when
		}
	}
?>