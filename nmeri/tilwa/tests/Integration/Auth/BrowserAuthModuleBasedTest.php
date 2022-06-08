<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Contracts\{Auth\AuthStorage, Config\Router};

	use Tilwa\Auth\Storage\SessionStorage;

	use Tilwa\Adapters\Orms\Eloquent\Models\User as EloquentUser;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Condiments\BaseDatabasePopulator};

	use Tilwa\Testing\Proxies\{WriteOnlyContainer, SecureUserAssertions};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Routes\Auth\SecureBrowserCollection, Config\RouterMock};

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

			$sut = $this->getContainer()->getClass(AuthStorage::class);

			$sut->logout(); // when

			$this->get("/segment")->assertUnauthorized(); // then
		}

		public function test_cant_access_api_auth_route_with_session () {

			$user = $this->replicator->getRandomEntity();

			$this->actingAs($user, SessionStorage::class); // given

			$this->get("/api/v1/segment") // when

			->assertUnauthorized(); // then
		}
	}
?>