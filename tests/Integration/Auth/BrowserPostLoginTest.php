<?php
	namespace Suphle\Tests\Integration\Auth;

	use Suphle\Contracts\Auth\AuthStorage;

	use Suphle\Auth\Storage\SessionStorage;

	use Suphle\Adapters\Orms\Eloquent\Models\User as EloquentUser;

	use Suphle\Testing\{Condiments\BaseDatabasePopulator, Proxies\SecureUserAssertions, TestTypes\IsolatedComponentTest};

	use Suphle\Tests\Integration\Generic\CommonBinds;

	class BrowserPostLoginTest extends IsolatedComponentTest {

		use BaseDatabasePopulator, SecureUserAssertions, CommonBinds {

			CommonBinds::simpleBinds as commonSimples;
		}

		protected function simpleBinds ():array {

			return array_merge($this->commonSimples(), [

				AuthStorage::class => SessionStorage::class // ensure we're working with session in this test although that's the default
			]);
		}

		protected function getActiveEntity ():string {

			return EloquentUser::class;
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