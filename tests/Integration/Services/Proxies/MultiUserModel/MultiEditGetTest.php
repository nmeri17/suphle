<?php
	namespace Suphle\Tests\Integration\Services\Proxies\MultiUserModel;

	use Suphle\Contracts\{Services\Models\IntegrityModel, Config\Router};

	use Suphle\Contracts\Modules\DescriptorInterface;

	use Suphle\Exception\Explosives\EditIntegrityException;

	use Suphle\Adapters\Orms\Eloquent\Models\User as EloquentUser;

	use Suphle\Testing\{TestTypes\InvestigateSystemCrash, Condiments\BaseDatabasePopulator};

	use Suphle\Testing\Proxies\{WriteOnlyContainer, SecureUserAssertions};

	use Suphle\Tests\Mocks\Models\Eloquent\MultiEditProduct;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\Auth\AuthorizeRoutes, Meta\ModuleOneDescriptor, Config\RouterMock};

	class MultiEditGetTest extends InvestigateSystemCrash {

		use BaseDatabasePopulator, SecureUserAssertions;

		protected $softenDisgraceful = true, $product;

		protected function getModule ():DescriptorInterface {

			return $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

				$container->replaceWithMock(Router::class, RouterMock::class, [

					"browserEntryRoute" => AuthorizeRoutes::class
				]);
			});
		}

		protected function getActiveEntity ():string {

			return MultiEditProduct::class;
		}

		public function test_unauthorized_getter_throws_error () {

			$this->assertWillCatchException(EditIntegrityException::class, function () { // then

				$this->get("admin/gmulti-edit-unauth"); // when
			});
		}

		protected function preDatabaseFreeze ():void {

			$this->product = $this->replicator->modifyInsertion(

				1, [], function ($builder) {

					return $builder->for(EloquentUser::factory()->state([

						"is_admin" => true
					]), "seller");
				}
			)->first();
		}

		public function test_authorized_getter_is_successful () {

			$this->actingAs($this->product->seller); // given

			// $this->debugCaughtException();

			$randomProduct = $this->replicator->getRandomEntity();

			$this->get("/admin/gmulti-edit/". $randomProduct->id) // when

			->assertOk(); // then
		}
	}
?>