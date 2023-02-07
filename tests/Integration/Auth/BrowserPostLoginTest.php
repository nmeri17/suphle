<?php
	namespace Suphle\Tests\Integration\Auth;

	use Suphle\Hydration\Container;

	use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

	use Suphle\Testing\{Condiments\BaseDatabasePopulator, Proxies\SecureUserAssertions, TestTypes\ModuleLevelTest};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	class BrowserPostLoginTest extends ModuleLevelTest {

		use BaseDatabasePopulator, SecureUserAssertions;

		protected function getModules ():array {

			return [
				new ModuleOneDescriptor (new Container)
			];
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