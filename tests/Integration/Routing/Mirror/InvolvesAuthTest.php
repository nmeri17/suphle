<?php
	namespace Suphle\Tests\Integration\Routing\Mirror;

	use Suphle\Contracts\Config\Router;

	use Suphle\Auth\Storage\TokenStorage;

	use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

	use Suphle\Testing\{Condiments\BaseDatabasePopulator, TestTypes\ModuleLevelTest};

	use Suphle\Testing\Proxies\{WriteOnlyContainer, SecureUserAssertions};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Routes\Auth\SecureBrowserCollection, Config\RouterMock};

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

			$requestToken = $this->actingAs($this->replicator->getRandomEntity(), $tokenClass); // given

			$this->get("/api/v1/segment", [

				TokenStorage::AUTHORIZATION_HEADER => "Bearer ". $requestToken
			]) // when
			->assertOk(); // then

			$this->assertInstanceOf($tokenClass, $this->getAuthStorage());
		}
	}
?>