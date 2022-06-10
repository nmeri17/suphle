<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Tests\Integration\Auth\Bases\BaseTestApiLoginRenderer;

	class FailedApiLoginRendererTest extends BaseTestApiLoginRenderer {

		protected function preDatabaseFreeze ():void {

			$this->sendIncorrectRequest(self::LOGIN_PATH); // given
		}

		public function test_failedLogin () {

			$this->injectLoginRenderer(0, 1); // then

			$this->evaluateLoginStatus(); // when
		}
	}
?>