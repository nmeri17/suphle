<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Condiments\PopulatesDatabaseTest, Proxies\WriteOnlyContainer};

	use Tilwa\Contracts\Auth\{AuthStorage, User};

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{ModuleOneDescriptor, Routes\Auth\SecureBrowserCollection, Config\RouterMock};

	class BrowserAuthModuleBasedTest extends ModuleLevelTest {

		use PopulatesDatabaseTest;

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

			return User::class;
		}

		public function test_cant_resume_auth_session_after_logout () {

			$user = $this->replicator->getRandomEntity();

			$this->actingAs($user); // given

			$sut = $this->container->getClass(AuthStorage::class);

			$sut->logout(); // when

			$this->get("/segment")

			->assertUnauthorized(); // then
		}
	}
?>