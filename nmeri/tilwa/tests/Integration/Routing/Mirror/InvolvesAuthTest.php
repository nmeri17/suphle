<?php
	namespace Tilwa\Tests\Integration\Routing\Mirror;

	use Tilwa\Testing\{Condiments\BaseDatabasePopulator, TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

	use Tilwa\Contracts\{Auth\UserContract, Config\Router};

	use Tilwa\Auth\Storage\TokenStorage;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Routes\Auth\SecureBrowserCollection, Config\RouterMock};

	class InvolvesAuthTest extends ModuleLevelTest {

		use BaseDatabasePopulator;

		protected function getModules():array {

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

			return UserContract::class;
		}

		public function test_auth_storage_changes () {

			$tokenClass = TokenStorage::class;

			$this->actingAs($this->replicator->getRandomEntity(), $tokenClass); // given

			$this->get("/api/v1/segment") // when

			->assertOk(); // then

			$this->assertInstanceOf($tokenClass, $this->getAuthStorage());
		}
	}
?>