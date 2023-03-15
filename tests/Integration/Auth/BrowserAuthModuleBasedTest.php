<?php
	namespace Suphle\Tests\Integration\Auth;

	use Suphle\Contracts\{Auth\AuthStorage, Config\Router};

	use Suphle\Auth\Storage\SessionStorage;

	use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Condiments\BaseDatabasePopulator};

	use Suphle\Testing\Proxies\{WriteOnlyContainer, SecureUserAssertions};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Routes\Auth\SecureBrowserCollection, Config\RouterMock};

	class BrowserAuthModuleBasedTest extends ModuleLevelTest {

		use BaseDatabasePopulator, SecureUserAssertions;

		protected function getModules ():array {

			return [
				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

							"browserEntryRoute" => SecureBrowserCollection::class
						]
					);
				})
			];
		}

		protected function getActiveEntity ():string {

			return EloquentUser::class;
		}

		public function test_cant_resume_auth_session_after_logout () {

			$user = $this->replicator->getRandomEntity();

			$this->actingAs($user); // given

			$this->get("/segment")->assertOk(); // sanity check

			$sut = $this->getContainer()->getClass(AuthStorage::class);

			$sut->logout(); // when

			$this->get("/segment")->assertUnauthorized(); // then
		}

		public function test_mirrored_route_detects_auth () {

			$this->markTestSkipped();

			$user = $this->replicator->getRandomEntity();

			$this->actingAs($user, SessionStorage::class); // given

			$this->get("/api/v1/segment") // when

			->assertUnauthorized(); // then
		}
	}
?>