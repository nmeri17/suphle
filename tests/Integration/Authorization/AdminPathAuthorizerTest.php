<?php
	namespace Suphle\Tests\Integration\Authorization;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Authorization\Paths\ModelEditRule;

	class AdminPathAuthorizerTest extends TestPathAuthorizer {

		private $user;

		protected function setUser ():void {

			$this->user = $this->makeUser(true);
		}

		public function test_present_authorization_succeeds () {

			$this->actingAs($this->user); // given

			$this->fakeRequest("/admin-entry"); // when

			$this->assertTrue($this->authorizationSuccess()); // then
		}

		public function test_present_nested_authorization_succeeds () {

			$this->actingAs($this->user); // given

			$this->fakeRequest("/admin/retain"); // when

			$this->assertTrue($this->authorizationSuccess()); // then
		}

		public function test_nested_can_add_more_locks () {

			$admin = $this->user; // must be an admin, otherwise the admin rule attached to the parent will cause it to fail

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
	}
?>