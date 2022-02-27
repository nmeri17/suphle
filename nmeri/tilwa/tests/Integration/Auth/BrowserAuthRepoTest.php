<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Auth\{Renderers\BrowserAuthRepo, Storage\SessionStorage};

	use Tilwa\Testing\Proxies\FrontDoorTest;

	class BrowserAuthRepoTest extends LoginRepoTest {

		use FrontDoorTest {FrontDoorTest::setUp as frontSetup};

		protected function setUp () {

			$this->frontSetup();
		}

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

		public function test_cant_access_api_auth_route_with_session () {

			$user = $this->replicator->getRandomEntity();

			$this->actingAs($user, SessionStorage::class); // given

			$this->get("/api/v1/secure-segment") // when

			->assertUnauthorized(); // then
		}
	}
?>