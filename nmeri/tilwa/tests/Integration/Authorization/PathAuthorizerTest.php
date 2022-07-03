<?php
	namespace Tilwa\Tests\Integration\Authorization;

	use Tilwa\Request\PathAuthorizer;

	use Tilwa\Bridge\Laravel\LaravelAppConcrete;

	use Tilwa\Adapters\Orms\Eloquent\OrmLoader;

	use Tilwa\Contracts\{Auth\UserContract, Database\OrmBridge};

	use Tilwa\Testing\{Proxies\SecureUserAssertions, Condiments\BaseDatabasePopulator};

	use Tilwa\Tests\Integration\Routing\TestsRouter;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Auth\AuthorizeRoutes, Authorization\Paths\ModelEditRule, InterfaceLoader\AdminableOrmLoader};

	use Tilwa\Tests\Mocks\Models\Eloquent\AdminableUser;

	class PathAuthorizerTest extends TestsRouter {

		use SecureUserAssertions, BaseDatabasePopulator;

		private $authorizer;
		
		protected function getEntryCollection ():string {

			return AuthorizeRoutes::class;
		}

		protected function getActiveEntity ():string {

			return AdminableUser::class;
		}

		protected function simpleBinds ():array {

			return array_merge(parent::simpleBinds(), [

				OrmLoader::class => AdminableOrmLoader::class // can't inject User instead since OrmDialect won't have booted, and we can't do hydrating work here, since that would mean possibly missing out on [parent::simpleBinds()]
			]);
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