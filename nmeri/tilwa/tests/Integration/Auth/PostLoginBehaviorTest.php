<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Testing\{PopulatesDatabaseTest, DirectHttpTest, BaseTest};

	use Tilwa\Tests\Mocks\Models\User;

	class PostLoginBehaviorTest {

		use PopulatesDatabaseTest, DirectHttpTest;

		protected function getActiveEntity ():string {

			return User::class;
		}

		// this uses populate db (trait version) and http tests ie go through the front controller, almost similar to what is done with module level tests
		public function test_cant_get_user_after_logout () {

			// given
			$this->insertNewUser(); // we want to auto-seed the db, grab a dummy to login with

			// when
			$this->login(); // we don't care about the response

			$this->assertInstanceOf(User::class, $this->httpGet("/auth/get-user" ));

			$this->httpPost("/logout" );

			// then
			$this->assertNull( $this->httpGet("/auth/get-user" ));
		}

		public function test_loginAs () { // login as x legitimately. send another request that loginAs y and confirm pulling user afterwards returns y

			$this->sendCorrectRequest(); // given

			// when
			$this->getLoginResponse();

			$sampleUser = $this->getRandomEntity();

			//
		}
	}
?>