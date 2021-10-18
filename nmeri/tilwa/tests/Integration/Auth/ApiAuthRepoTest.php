<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Testing\{PopulatesDatabaseTest, IsolatedComponentTest, ExaminesHttpResponse};

	use Tilwa\Tests\Mocks\Models\User;

	use Tilwa\Auth\{ApiLoginRenderer, LoginRequestHandler, ApiAuthRepo};

	use Illuminate\Testing\TestResponse;

	class ApiAuthRepoTest extends IsolatedComponentTest {

		use PopulatesDatabaseTest, ExaminesHttpResponse {

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

			$response->assertJson( $sut->successLogin()); // then
		}

		public function test_failedLogin () {

			$this->userInserter->sendIncorrectRequest(); // given

			$response = $this->getLoginResponse(); // when

			$sut = $this->container->getClass(ApiAuthRepo::class);

			$response->assertJson( $sut->failedLogin()); // then
		}

		private function getLoginResponse ():TestResponse {

			$identifier = $this->getIdentifier();

			$identifier->getResponse();

			return $this->makeExaminable($identifier->handlingRenderer());
		}

		private function getIdentifier ():LoginRequestHandler {

			$container = $this->container;

			$identifierName = LoginRequestHandler::class;

			$collection = $container->getClass(ApiLoginRenderer::class);

			return $container->whenType($identifierName)

			->needsArguments(compact("collection"))

			->getClass($identifierName);
		}

		public function test_route_mirroring_works () {

			// we want to test detection of browser routes and that we can override from our own end as well
		}

		public function test_route_mirroring_on_index_affects_all () {

			//
		}
	}
?>