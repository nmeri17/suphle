<?php
	namespace Tilwa\Tests\Integration\Routing\Mirror;

	use Tilwa\Testing\{Condiments\PopulatesDatabaseTest, Proxies\FrontDoorTest, TestTypes\ModuleLevelTest};

	use Tilwa\Contracts\Auth\User;

	use Tilwa\Auth\Storage\TokenStorage;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{ModuleOneDescriptor, Routes\Auth\SecureBrowserCollection};

	class InvolvesAuthTest extends ModuleLevelTest {

		use FrontDoorTest, PopulatesDatabaseTest;

		protected function getModules():array {

			return [
				$this->replicateModule(ModuleOneDescriptor::class, function (Container $container) {

					$container->whenTypeAny()->needsAny([

						IRouter::class => $this->positiveMock(
							RouterMock::class,

							[
								"browserEntryRoute" => SecureBrowserCollection::class
							]
						)
					]);
				})
			];
		}

		protected function getActiveEntity ():string {

			return User::class;
		}

		public function test_auth_storage_changes () {

			$tokenClass = TokenStorage::class;

			$this->actingAs($this->getRandomEntity(), $tokenClass); // given

			$this->get("/api/v1/segment") // when

			->assertOk(); // then

			$this->assertInstanceOf($tokenClass, $this->getAuthStorage());
		}
	}
?>