<?php
	namespace Suphle\Tests\Integration\Authorization;

	use Suphle\Request\PathAuthorizer;

	use Suphle\Bridge\Laravel\LaravelAppConcrete;

	use Suphle\Adapters\Orms\Eloquent\{OrmLoader, Models\User as EloquentUser};

	use Suphle\Contracts\{Auth\UserContract, Database\OrmBridge};

	use Suphle\Testing\{Proxies\SecureUserAssertions, Condiments\BaseDatabasePopulator};

	use Suphle\Tests\Integration\Routing\TestsRouter;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\Auth\AuthorizeRoutes, Authorization\Paths\ModelEditRule};

	class PathAuthorizerTest extends TestsRouter {

		use SecureUserAssertions, BaseDatabasePopulator;

		private $authorizer;
		
		protected function getEntryCollection ():string {

			return AuthorizeRoutes::class;
		}

		protected function getActiveEntity ():string {

			return EloquentUser::class;
		}

		private function makeUser (bool $makeAdmin = false):UserContract {

			return $this->replicator->modifyInsertion(1, [

				"is_admin" => $makeAdmin
			])->first();
		}

		// can't move this to setUp since this object is updated after request is updated
		private function getAuthorizer ():PathAuthorizer {

			return $this->container->getClass(PathAuthorizer::class);
		}
		private function authorizationSuccess ():bool {

			return $this->getAuthorizer()->passesActiveRules();
		}

		public function test_present_authorization_succeeds () {

			$this->actingAs($this->makeUser(true)); // given

			$this->fakeRequest("/admin-entry"); // when

			$this->assertTrue($this->authorizationSuccess()); // then
		}

		public function test_absent_authorization_fails () {

			$this->actingAs($this->makeUser()); // given

			$this->fakeRequest("/admin-entry"); // when

			$this->assertFalse($this->authorizationSuccess()); // then
		}

		public function test_present_nested_authorization_succeeds () {

			$this->actingAs($this->makeUser(true)); // given

			$this->fakeRequest("/admin/retain"); // when

			$this->assertTrue($this->authorizationSuccess()); // then
		}

		public function test_absent_nested_authorization_fails () {

			$this->actingAs($this->makeUser()); // given

			$this->fakeRequest("/admin/retain"); // when

			$this->assertFalse($this->authorizationSuccess()); // then
		}

		public function test_nested_can_add_more_locks () {

			$admin = $this->makeUser(true); // must be an admin, otherwise the admin rule attached to the parent will cause it to fail

			$this->actingAs($admin); // given

			$model = new class {

				public $creatorId;

				public function getCreatorId ():int {

					return $this->creatorId;
				}
			};

			$model->creatorId = $admin->getId();

			$this->container->whenType(ModelEditRule::class)

			->needsArguments([ "modelService" => $model ]);

			$this->fakeRequest("/admin/additional-rule"); // when

			$this->assertTrue($this->authorizationSuccess()); // then
		}

		public function test_nested_missing_all_rules_fails () {

			// given
			$this->actingAs($this->makeUser());

			$this->container->whenType(ModelEditRule::class)

			->needsArguments([

				"modelService" => new class {

					public function getCreatorId ():int {

						return 99;
					}
				}
			]);

			$this->fakeRequest("/admin/additional-rule"); // when

			$this->assertFalse($this->authorizationSuccess()); // then
		}

		public function test_unlock_works () {

			$this->actingAs($this->makeUser()); // given

			$this->fakeRequest("/admin/secede"); // when

			$this->assertTrue($this->authorizationSuccess()); // then
		}

		public function test_unlock_returns_empty_rule_list () {

			$this->actingAs($this->makeUser()); // given

			$this->fakeRequest("/admin/secede"); // when

			$result = $this->getAuthorizer()->getActiveRules();

			$this->assertEmpty($result); // then
		}
	}
?>