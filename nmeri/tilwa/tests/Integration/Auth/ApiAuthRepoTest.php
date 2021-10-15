<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Testing\{PopulatesDatabaseTest, DirectHttpTest, IsolatedComponentTest};

	use Tilwa\Tests\Mocks\Models\User;

	use Tilwa\Auth\{ApiLoginRenderer, LoginRequestHandler, ApiAuthRepo};

	class ApiAuthRepoTest extends IsolatedComponentTest {

		use PopulatesDatabaseTest {

			PopulatesDatabaseTest::setUp as populateDB
		}

		private $userInserter;

		public function setUp {

			$this->populateDB();

			$this->userInserter = new UserInserter;
		}

		protected function getActiveEntity ():string {

			return User::class;
		}

		public function test_successLogin () {

			$this->userInserter->sendCorrectRequest(); // given

			$response = $this->getLoginResponse(); // when

			$sut = $this->container->getClass(ApiAuthRepo::class);

			$this->assertSame($response, $sut->successLogin()); // then
		}

		public function test_failedLogin () {

			$this->userInserter->sendIncorrectRequest(); // given

			$response = $this->getLoginResponse(); // when

			$sut = $this->container->getClass(ApiAuthRepo::class);

			// then
			$this->assertSame($response, $sut->failedLogin()); // note, this won't work since that response has already been converted to a string. find a way to either work with the renderer or wrap it in a testResponse
		}

		private function getLoginResponse ():array {

			$container = $this->container;

			$renderer = $container->getClass(ApiLoginRenderer::class);

			return (new LoginRequestHandler($renderer, $container))->getResponse();
		}

		public function test_route_mirroring_works () {

			// we want to test detection of browser routes and that we can override from our own end as well
		}

		public function test_route_mirroring_on_index_affects_all () {

			//
		}
	}
?>