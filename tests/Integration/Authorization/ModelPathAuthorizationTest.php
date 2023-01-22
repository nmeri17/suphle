<?php
	namespace Suphle\Tests\Integration\Authorization;

	use Suphle\Contracts\{Config\Router, Auth\UserContract};

	use Suphle\Adapters\Orms\Eloquent\Models\User as EloquentUser;

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Condiments\BaseDatabasePopulator};

	use Suphle\Testing\Proxies\{WriteOnlyContainer, SecureUserAssertions};

	use Suphle\Tests\Mocks\Models\Eloquent\{Employment, Employer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\Auth\AuthorizeRoutes, Meta\ModuleOneDescriptor, Config\RouterMock};

	class ModelPathAuthorizationTest extends ModuleLevelTest {

		use BaseDatabasePopulator, SecureUserAssertions;

		protected const EDIT_PATH = "/admin/gmulti-edit/";

		protected Employment $employment;
		
		protected UserContract $admin;
		
		protected int $randomEmploymentId;

		protected function getModules ():array {

			return [
				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => AuthorizeRoutes::class
					]);
				})
			];
		}

		protected function getActiveEntity ():string {

			return Employment::class;
		}

		protected function preDatabaseFreeze ():void {

			$this->replicator->modifyInsertion(10); // using this since the seeded ones will be cleared by the time request is sent

			$this->randomEmploymentId = $this->replicator->getRandomEntity()->id;

			$this->employment = $this->replicator->modifyInsertion( // User must be an admin, otherwise the admin rule attached to the outer prefix will cause requests to fail

				1, [], function ($builder) {

					$employer = Employer::factory()

					->for(EloquentUser::factory()->state([

						"is_admin" => true
					]))->create();

					return $builder->for($employer);
				}
			)->first();

			$this->admin = $this->employment->employer->user;
		}

		public function test_nested_can_add_more_locks () {

			$this->actingAs($this->admin); // given

			$this->get(self::EDIT_PATH . $this->employment->id) // when

			->assertOk(); // then
		}

		public function test_nested_missing_all_rules_fails () {

			$this->actingAs($this->admin); // given

			$this->get(self::EDIT_PATH . $this->randomEmploymentId) // when

			->assertForbidden(); // then
		}
	}
?>