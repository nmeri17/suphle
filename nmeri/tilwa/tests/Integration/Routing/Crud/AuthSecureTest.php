<?php
	namespace Tilwa\Tests\Integration\Routing\Crud;

	use Tilwa\Tests\Integration\Routing\BaseRouterTest;

	use Tilwa\Testing\{Proxies\FrontDoorTest, Condiments\PopulatesDatabaseTest, TestTypes\ModuleLevelTest};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{ModuleOneDescriptor, Routes\Crud\AuthenticateCrudCollection};

	use Tilwa\Contracts\Auth\User;

	use Tilwa\App\Container;

	class AuthSecureTest extends ModuleLevelTest {

		use FrontDoorTest, PopulatesDatabaseTest;

		protected function getModules():array {

			return [
				$this->replicateModule(ModuleOneDescriptor::class, function (Container $container) {

					$container->whenTypeAny()->needsAny([

						IRouter::class => $this->positiveMock(
							RouterMock::class,

							[
								"browserEntryRoute" => AuthenticateCrudCollection::class
							]
						)
					]);
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

			$this->actingAs($this->getRandomEntity()) // given

			->get("/secure-some/edit/5") // when

			->assertOk(); // then
		}
	}
?>