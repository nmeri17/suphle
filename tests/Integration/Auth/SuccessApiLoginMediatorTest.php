<?php
	namespace Suphle\Tests\Integration\Auth;

	use Suphle\Tests\Integration\Auth\Bases\BaseTestApiLoginMediator;

	class SuccessApiLoginMediatorTest extends BaseTestApiLoginMediator {

		protected function preDatabaseFreeze ():void {

			$this->sendCorrectRequest(self::LOGIN_PATH); // given
		}

		public function test_successLogin () {

			$this->injectLoginMediator(1, 0); // then

			$this->evaluateLoginStatus(); // when
		}
	}
?>