<?php
	namespace Tilwa\Tests\Integration\Routing\Crud;

	use Tilwa\Testing\{Condiments\PopulatesDatabaseTest, TestTypes\ModuleLevelTest};

	use Tilwa\Testing\Proxies\{FrontDoorTest, WriteOnlyContainer};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{ModuleOneDescriptor, Routes\Crud\AuthenticateCrudCollection, Config\RouterMock};

	use Tilwa\Contracts\{Auth\User, Config\Router};

	class AuthSecureTest extends ModuleLevelTest {

		use FrontDoorTest, PopulatesDatabaseTest;

		protected function getModules():array {

			return [
				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

							"browserEntryRoute" => AuthenticateCrudCollection::class
						]
					);
				})
			];
		}

		protected function getActiveEntity ():string {

			return User::class;
		}

		public function test_no_authenticated_user_throws_error () {

			$this->get("/secure-some/edit/5") // when

			->assertUnauthorized(); // then
		}

		public function test_with_authentication_throws_no_error () {

			$this->actingAs($this->replicator->getRandomEntity()) // given

			->get("/secure-some/edit/5") // when

			->assertOk(); // then
		}
	}
?>