<?php
	namespace Suphle\Tests\Integration\Auth;

	use Suphle\Tests\Integration\Auth\Bases\BaseTestApiLoginRenderer;

	class SuccessApiLoginRendererTest extends BaseTestApiLoginRenderer {

		protected function preDatabaseFreeze ():void {

			$this->sendCorrectRequest(self::LOGIN_PATH); // given
		}

		public function test_successLogin () {

			$this->injectLoginRenderer(1, 0); // then

			$this->evaluateLoginStatus(); // when
		}
	}
?>