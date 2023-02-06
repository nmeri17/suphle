<?php
	namespace Suphle\Tests\Integration\Auth;

	use Suphle\Tests\Integration\Auth\Bases\BaseTestApiLoginMediator;

	class FailedApiLoginMediatorTest extends BaseTestApiLoginMediator {

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