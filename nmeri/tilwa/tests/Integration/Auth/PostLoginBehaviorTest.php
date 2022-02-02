<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Testing\Condiments\{PopulatesDatabaseTest, IsolatedComponentSecurity};

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Contracts\Auth\{AuthStorage, User};

	use Tilwa\Auth\SessionStorage;

	class PostLoginBehaviorTest extends IsolatedComponentTest {

		use PopulatesDatabaseTest, IsolatedComponentSecurity;

		protected function getActiveEntity ():string {

			return User::class;
		}

		public function test_session_impersonate () {

			[$user1, $user2] = $this->getRandomEntities(2);

			$this->actingAs($user1); // given

			$sut = $this->container->getClass(SessionStorage::class);

			$sut->impersonate($user2->getId()); // when

			$this->assertAuthenticatedAs($user2); // then

			$this->assertSame($sut->getPreviousUser(), $user1->getId());
		}

		public function test_logout () {

			$user = $this->getRandomEntity();

			$this->actingAs($user); // given

			$sut = $this->container->getClass(AuthStorage::class);

			$sut->logout(); // when

			$this->assertGuest(); // then
		}

		public function test_can_resume_auth_session () {

			// login/start a new session. run ResponseManager::requestAuthenticationStatus and assert true
		}
	}
?>