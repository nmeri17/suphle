<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Auth\Renderers\BrowserAuthRepo;

	class BrowserAuthRepoTest extends LoginRepoTest {

		private $loginPath = "/login";

		public function test_successLogin () {

			$this->sendCorrectRequest($this->loginPath); // given

			$response = $this->getLoginResponse(); // when

			$sut = $this->container->getClass(BrowserAuthRepo::class);

			$response->assertSee( $sut->successLogin()["message"]); // then
		}

		public function test_failedLogin () {

			$this->sendIncorrectRequest($this->loginPath); // given

			$response = $this->getLoginResponse(); // when

			$sut = $this->container->getClass(BrowserAuthRepo::class);

			$response->assertSee( $sut->failedLogin()["message"]); // then
		}

		public function test_get_user_on_unauth_route_yields_from_default_storage () {

			//
		}

		public function test_cant_access_api_auth_route_with_session () {

			//
		}
	}
?>