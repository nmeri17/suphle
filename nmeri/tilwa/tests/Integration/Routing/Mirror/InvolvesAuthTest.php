<?php
	namespace Tilwa\Tests\Integration\Routing\Mirror;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Auth\Storage\TokenStorage;

	use Tilwa\Adapters\Orms\Eloquent\Models\User as EloquentUser;

	use Tilwa\Testing\{Condiments\BaseDatabasePopulator, TestTypes\ModuleLevelTest};

	use Tilwa\Testing\Proxies\{WriteOnlyContainer, SecureUserAssertions};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Routes\Auth\SecureBrowserCollection, Config\RouterMock};

	class InvolvesAuthTest extends ModuleLevelTest {

		use BaseDatabasePopulator, SecureUserAssertions;

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

			return EloquentUser::class;
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