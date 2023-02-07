<?php
	namespace Suphle\Tests\Integration\Routing\Crud;

	use Suphle\Contracts\Config\Router;

	use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

	use Suphle\Testing\{Condiments\BaseDatabasePopulator, TestTypes\ModuleLevelTest};

	use Suphle\Testing\Proxies\{SecureUserAssertions, WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Routes\Crud\AuthenticateCrudCollection, Config\RouterMock};

	class AuthSecureTest extends ModuleLevelTest {

		use BaseDatabasePopulator, SecureUserAssertions;

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

			return EloquentUser::class;
		}

		public function test_no_authenticated_user_throws_error () {

			$this->get("/secure-some/edit/5") // when

			->assertUnauthorized(); // then
		}

		public function test_with_authentication_throws_no_error () {

			$this->actingAs($this->replicator->getRandomEntity()); // given

			$this->get("/secure-some/edit/5") // when

			->assertOk(); // then
		}
	}
?>