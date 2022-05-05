<?php
	namespace Tilwa\Tests\Integration\Authorization;

	use Tilwa\Request\PathAuthorizer;

	use Tilwa\Bridge\Laravel\LaravelAppConcrete;

	use Tilwa\Adapters\Orms\Eloquent\OrmLoader;

	use Tilwa\Contracts\{Auth\UserContract, Database\OrmBridge};

	use Tilwa\Testing\{Proxies\SecureUserAssertions, Condiments\BaseDatabasePopulator};

	use Tilwa\Tests\Integration\Routing\TestsRouter;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Auth\AuthorizeRoutes, Authorization\Paths\ModelEditRule, Adapters\AdminableOrmBridge, InterfaceLoader\AdminableOrmLoader};

	use Tilwa\Tests\Mocks\Models\Eloquent\AdminableUser;

	class PathAuthorizerTest extends TestsRouter {

		use SecureUserAssertions, BaseDatabasePopulator;

		protected function getInitialCount ():int {

			return 10;
		}
		
		protected function getEntryCollection ():string {

			return AuthorizeRoutes::class;
		}

		protected function getActiveEntity ():string {

			return AdminableUser::class;
		}

		protected function simpleBinds ():array {

			return array_merge(parent::simpleBinds(), [

				OrmLoader::class => AdminableOrmLoader::class
			]);
		}

		private function getUser67 (bool $makeAdmin = false):UserContract {

			return $this->replicator->modifyInsertion(1, [

				"is_admin" => $makeAdmin
			])->first();
		}

		private function authorizationSuccess ():bool {

			return $this->container->getClass(PathAuthorizer::class)->passesActiveRules();
		}

		public function test_present_authorization_succeeds () {

			$this->actingAs($this->getUser67(true)); // given

			$this->fakeRequest("/admin-entry"); // when

			$this->assertTrue($this->authorizationSuccess()); // then
		}

		public function test_absent_authorization_fails () {

			$this->actingAs($this->getUser67()); // given

			$this->fakeRequest("/admin-entry"); // when

			$this->assertFalse($this->authorizationSuccess()); // then
		}

		public function test_present_nested_authorization_succeeds () {

			$this->actingAs($this->getUser67(true)); // given

			$this->fakeRequest("/admin/retain"); // when

			$this->assertTrue($this->authorizationSuccess()); // then
		}

		public function test_absent_nested_authorization_fails () {

			$this->actingAs($this->getUser67()); // given

			$this->fakeRequest("/admin/retain"); // when

			$this->assertFalse($this->authorizationSuccess()); // then
		}

		public function test_nested_can_add_more_locks () {

			// given
			$this->actingAs($this->getUser67());

			$this->container->whenType(ModelEditRule::class)

			->needsArguments([

				"modelService" => new class {

					public function getCreatorId ():int {

						return 67;
					}
				}
			]);

			$this->fakeRequest("/admin/additional-rule"); // when

			$this->assertTrue($this->authorizationSuccess()); // then
		}

		public function test_nested_missing_all_rules_fails () {

			// given
			$this->actingAs($this->getUser67());

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

			$this->actingAs($this->getUser67()); // given

			$this->fakeRequest("/admin/secede"); // when

			$this->assertTrue($this->authorizationSuccess()); // then
		}
	}
?>