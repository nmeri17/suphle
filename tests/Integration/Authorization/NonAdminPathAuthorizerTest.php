<?php
	namespace Suphle\Tests\Integration\Authorization;

	class NonAdminPathAuthorizerTest extends TestPathAuthorizer {

		private $user;

		protected function setUser ():void {

			$this->user = $this->makeUser();
		}

		public function test_absent_authorization_fails () {

			$this->actingAs($this->user); // given

			$this->fakeRequest("/admin-entry"); // when

			$this->assertFalse($this->authorizationSuccess()); // then
		}

		public function test_absent_nested_authorization_fails () {

			$this->actingAs($this->user); // given

			$this->fakeRequest("/admin/retain"); // when

			$this->assertFalse($this->authorizationSuccess()); // then
		}

		public function test_unlock_works () {

			$this->actingAs($this->user); // given

			$this->fakeRequest("/admin/secede"); // when

			$this->assertTrue($this->authorizationSuccess()); // then
		}

		public function test_unlock_returns_empty_rule_list () {

			$this->actingAs($this->user); // given

			$this->fakeRequest("/admin/secede"); // when

			$result = $this->getAuthorizer()->getActiveRules();

			$this->assertEmpty($result); // then
		}
	}
?>