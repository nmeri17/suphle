<?php

	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Testing\PopulatesDatabaseTest;

	use Tilwa\Tests\Mocks\Models\User;

	use Tilwa\Auth\{ApiLoginRenderer, LoginRequestHandler, ApiAuthRepo};

	class ApiAuthRepoTest extends PopulatesDatabaseTest { // note: if routing isn't working, recall this is/should be using a separate router
		private $correctPassword = "liquidmetal",

		$incorrectPassword = "goldenboy";

		protected function getActiveEntity ():string {

			return User::class;
		}

		public function test_successLogin () {

			$this->sendCorrectRequest(); // given

			$container = $this->container;

			$renderer = $container->getClass(ApiLoginRenderer::class);

			$response = (new LoginRequestHandler($renderer, $container))->getResponse(); // when

			$sut = $container->getClass(ApiAuthRepo::class);

			// then
			$this->assertSame($response, $sut->successLogin());
		}

		private function getInsertedUser (string $password):User {
			
			$user = $this->getBeforeInsertion(1, [ // inserting a new row rather than pulling a random one so we can access the "password" field during login request

				"password" => password_hash($password, PASSWORD_DEFAULT)
			]);

			$user->save();

			return $user;
		}

		private function sendCorrectRequest ():void {

			$user = $this->getInsertedUser($this->correctPassword);

			$this->sendJsonPayload("api/v1/login", [

				"email" => $user->email,

				"password" => $this->correctPassword
			]);
		}

		private function sendIncorrectRequest ():void {

			$user = $this->getInsertedUser($this->correctPassword);

			$this->sendJsonPayload("api/v1/login", [

				"email" => $user->email,

				"password" => $this->incorrectPassword
			]);
		}

		public function test_failedLogin () {

			$this->sendIncorrectRequest(); // given

			$container = $this->container;

			$renderer = $container->getClass(ApiLoginRenderer::class);

			$response = (new LoginRequestHandler($renderer, $container))->getResponse(); // when

			$sut = $container->getClass(ApiAuthRepo::class);

			// then
			$this->assertSame($response, $sut->failedLogin());
		}

		public function test_logout () {

			// confirm that running this empties what was stored earlier i.e. two different requests ought to be made inside this
		}

		public function test_loginAs () {

			//
		}

		public function test_route_mirroring_works () {

			//
		}

		public function test_route_mirroring_on_index_affects_all () {

			//
		}
	}
?>