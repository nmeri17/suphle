<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Auth\{Renderers\BrowserAuthRepo, Storage\SessionStorage};

	class BrowserAuthRepoTest extends LoginRepoTest {

		const LOGIN_PATH = "/login";

		public function test_successLogin () {

			$this->sendCorrectRequest(self::LOGIN_PATH); // given

			$response = $this->getLoginResponse(); // when

			$sut = $this->container->getClass(BrowserAuthRepo::class);

			$response->assertSee( $sut->successLogin()["message"]); // then
		}

		public function test_failedLogin () {

			$this->sendIncorrectRequest(self::LOGIN_PATH); // given

			$response = $this->getLoginResponse(); // when

			$sut = $this->container->getClass(BrowserAuthRepo::class);

			$response->assertSee( $sut->failedLogin()["message"]); // then
		}

		public function test_cant_access_api_auth_route_with_session () {

			$user = $this->replicator->getRandomEntity();

			$this->actingAs($user, SessionStorage::class); // given

			$this->get("/api/v1/secure-segment") // when

			->assertUnauthorized(); // then
		}
	}
?>