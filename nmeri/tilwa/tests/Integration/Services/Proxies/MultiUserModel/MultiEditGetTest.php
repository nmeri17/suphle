<?php
	namespace Tilwa\Tests\Integration\Services\Proxies\MultiUserModel;

	use Tilwa\Services\DecoratorHandlers\MultiUserEditHandler;

	use Tilwa\Contracts\{Services\Models\IntegrityModel, Config\Router};

	use Tilwa\Contracts\Modules\DescriptorInterface;

	use Tilwa\Exception\Explosives\EditIntegrityException;

	use Tilwa\Adapters\Orms\Eloquent\OrmLoader;

	use Tilwa\Testing\TestTypes\InvestigateSystemCrash;

	use Tilwa\Testing\Proxies\{WriteOnlyContainer, SecureUserAssertions};

	use Tilwa\Testing\Condiments\BaseDatabasePopulator;

	use Tilwa\Tests\Mocks\Models\Eloquent\{MultiEditProduct, AdminableUser};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Auth\AuthorizeRoutes, Meta\ModuleOneDescriptor, Config\RouterMock, InterfaceLoader\AdminableOrmLoader};

	class MultiEditGetTest extends InvestigateSystemCrash {

		use BaseDatabasePopulator, SecureUserAssertions;

		//protected $softenDisgraceful = true;

		protected function getModule ():DescriptorInterface {

			return $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

				$container->replaceWithMock(Router::class, RouterMock::class, [

					"browserEntryRoute" => AuthorizeRoutes::class
				]);
			}, false);
		}

		protected function getActiveEntity ():string {

			return MultiEditProduct::class;
		}

		private function provideConcretes ():void {

			$this->massProvide([
				OrmLoader::class =>

				// replaceWithConcrete can't be combined with replaceConstructorArguments
				$this->replaceConstructorArguments(AdminableOrmLoader::class, []) // for authorizer to read adminable users
			]);
		}

		public function test_unauthorized_getter_throws_error () {

			$this->assertWillCatchException(EditIntegrityException::class, function () { // then

				$this->provideConcretes();

				$this->get("admin/gmulti-edit-unauth"); // when
			});
		}

		public function test_authorized_getter_is_successful () {

			$product = $this->replicator->modifyInsertion(

				1, [], function ($builder) {

					return $builder->for(AdminableUser::factory()->state([

						"is_admin" => true
					]), "seller");
				}
			)->first();

			$this->provideConcretes();
// if this doesn't work, use predbfreeze
			$this->actingAs($product->seller); // given

			$this->get("/admin/gmulti-edit-auth") // when

			->assertOk(); // then
		}
	}
?>