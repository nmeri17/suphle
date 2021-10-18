<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Testing\{PopulatesDatabaseTest, IsolatedComponentSecurity, IsolatedComponentTest};

	use Tilwa\Tests\Mocks\Models\User;

	use Tilwa\Auth\SessionStorage;

	use Tilwa\Contracts\Auth\AuthStorage;

	class PostLoginBehaviorTest extends IsolatedComponentTest {

		use PopulatesDatabaseTest, IsolatedComponentSecurity;

		protected function getActiveEntity ():string {

			return User::class;
		}

		public function test_session_loginAs () {

			[$user1, $user2] = $this->getRandomEntities(2);

			$this->actingAs($user1); // given

			$sut = $this->container->getClass(SessionStorage::class);

			$sut->loginAs($user2->getId()); // when

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
	}
?>