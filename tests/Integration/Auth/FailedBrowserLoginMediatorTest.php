<?php
	namespace Suphle\Tests\Integration\Auth;

	use Suphle\Tests\Integration\Auth\Bases\BaseTestBrowserLoginMediator;

	class FailedBrowserLoginMediatorTest extends BaseTestBrowserLoginMediator {

		protected function setUp ():void {

			parent::setUp();

			$this->sendIncorrectRequest(self::LOGIN_PATH); // given
		}

		public function test_failedLogin () {

			$this->bindAuthStatusObserver(0, 1); // then

			$this->evaluateLoginStatus(); // when
		}
	}
?>