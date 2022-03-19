<?php
	namespace Tilwa\Tests\Integration\Authorization;

	use Tilwa\Request\PathAuthorizer;

	use Tilwa\Testing\Proxies\SecureUserAssertions;

	use Tilwa\Tests\Integration\Routing\TestsRouter;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Auth\AuthorizeRoutes, Authorization\Paths\ModelEditRule};

	use Tilwa\Tests\Mocks\Models\Eloquent\AdminableUser;

	class PathAuthorizerTest extends TestsRouter {

		use SecureUserAssertions;
		
		protected function getEntryCollection ():string {

			return AuthorizeRoutes::class;
		}

		private function getUser67 (bool $makeAdmin = false) {

			return $this->positiveDouble(AdminableUser::class, [

				"isAdmin" => $makeAdmin,

				"getId" => 67
			]);
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