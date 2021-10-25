<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Testing\{Condiments\PopulatesDatabaseTest, TestTypes\IsolatedComponentTest, Proxies\ExaminesHttpResponse};

	use Tilwa\Contracts\Auth\User;

	use Tilwa\Auth\Renderers\{ApiLoginRenderer, ApiAuthRepo};

	use Tilwa\Auth\LoginRequestHandler;

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
	}
?>