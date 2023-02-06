<?php
	namespace Suphle\Tests\Integration\Auth;

	use Suphle\Tests\Integration\Auth\Bases\BaseTestBrowserLoginMediator;

	class SuccessBrowserLoginMediatorTest extends BaseTestBrowserLoginMediator {

		protected function setUp ():void {

			parent::setUp();

			$this->sendCorrectRequest(self::LOGIN_PATH); // given
		}

		public function test_successLogin () {

			$this->bindAuthStatusObserver(1, 0); // then

			$this->evaluateLoginStatus(); // when
		}
	}
?>