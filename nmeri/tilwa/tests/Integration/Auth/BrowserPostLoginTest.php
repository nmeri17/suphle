<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Contracts\Auth\AuthStorage;

	use Tilwa\Auth\Storage\SessionStorage;

	use Tilwa\Adapters\Orms\Eloquent\Models\User as EloquentUser;

	use Tilwa\Testing\{Condiments\BaseDatabasePopulator, Proxies\SecureUserAssertions, TestTypes\IsolatedComponentTest};

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	class BrowserPostLoginTest extends IsolatedComponentTest {

		use BaseDatabasePopulator, SecureUserAssertions, CommonBinds {

			CommonBinds::simpleBinds as commonSimples;
		}

		private $genericStorage = AuthStorage::class;

		protected function simpleBinds ():array {

			return array_merge($this->commonSimples(), [

				$this->genericStorage => SessionStorage::class // ensure we're working with session in this test although that's the default
			]);
		}

		protected function getActiveEntity ():string {

			return EloquentUser::class;
		}

		private function getAuthStorage ():AuthStorage {

			return $this->container->getClass($this->genericStorage);
		}

		public function test_session_impersonate () {

			[$user1, $user2] = $this->replicator->getRandomEntities(2);

			$this->actingAs($user1); // given

			$sut = $this->getAuthStorage();

			$sut->imitate($user2->getId()); // when

			$this->assertAuthenticatedAs($user2); // then

			$this->assertTrue($sut->getPreviousUser() == $user1->getId()); // int/string comparison
		}

		public function test_logout () {

			$user = $this->replicator->getRandomEntity();

			$this->actingAs($user); // given

			$sut = $this->getAuthStorage();

			$sut->logout(); // when

			$this->assertGuest(); // then
		}
	}
?>