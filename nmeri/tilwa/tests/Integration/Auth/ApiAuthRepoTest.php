<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Auth\Renderers\ApiAuthRepo;

	class ApiAuthRepoTest extends TestLoginRepo {

		private $loginPath = "/api/v1/login";

		public function test_successLogin () {

			$this->sendCorrectRequest($this->loginPath); // given

			$response = $this->getLoginResponse(); // when

			$sut = $this->container->getClass(ApiAuthRepo::class);

			$response->assertJson( $sut->successLogin()); // then
		}

		public function test_failedLogin () {

			$this->sendIncorrectRequest($this->loginPath); // given

			$response = $this->getLoginResponse(); // when

			$sut = $this->container->getClass(ApiAuthRepo::class);

			$response->assertJson( $sut->failedLogin()); // then
		}
	}
?>