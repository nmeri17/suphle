<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Testing\{Condiments\BaseDatabasePopulator, Proxies\SecureUserAssertions, TestTypes\IsolatedComponentTest};

	use Tilwa\Contracts\Auth\{AuthStorage, User};

	use Tilwa\Auth\SessionStorage;

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	class BrowserPostLoginTest extends IsolatedComponentTest {

		use BaseDatabasePopulator, SecureUserAssertions, CommonBinds;

		protected function getActiveEntity ():string {

			return User::class;
		}

		public function test_session_impersonate () {

			[$user1, $user2] = $this->replicator->getRandomEntities(2);

			$this->actingAs($user1); // given

			$sut = $this->container->getClass(SessionStorage::class);

			$sut->imitate($user2->getId()); // when

			$this->assertAuthenticatedAs($user2); // then

			$this->assertSame($sut->getPreviousUser(), $user1->getId());
		}

		public function test_logout () {

			$user = $this->replicator->getRandomEntity();

			$this->actingAs($user); // given

			$sut = $this->container->getClass(AuthStorage::class);

			$sut->logout(); // when

			$this->assertGuest(); // then
		}
	}
?>