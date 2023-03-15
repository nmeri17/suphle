<?php
	namespace Suphle\Tests\Integration\Authorization;

	use Suphle\Routing\PreMiddlewareRegistry;

	class NonAdminPathAuthorizerTest extends TestPathAuthorizer {

		private $user;

		protected function setUser ():void {

			$this->user = $this->makeUser();
		}

		public function test_absent_authorization_fails () {

			$this->actingAs($this->user); // given

			$this->get("/admin-entry"); // when

			$this->assertFalse($this->authorizationSuccess()); // then
		}

		public function test_absent_nested_authorization_fails () {

			$this->actingAs($this->user); // given

			$this->get("/admin/retain"); // when

			$this->assertFalse($this->authorizationSuccess()); // then
		}

		public function test_unlock_works () {

			$this->actingAs($this->user); // given

			$this->get("/admin/secede"); // when

			$this->assertTrue($this->authorizationSuccess()); // then
		}

		public function test_unlock_returns_empty_rule_list () {

			$this->actingAs($this->user); // given

			$this->get("/admin/secede"); // when

			$routedFunnels = $this->getContainer()->getClass(PreMiddlewareRegistry::class)

			->getRoutedFunnels();

			$this->assertEmpty($routedFunnels); // then
		}
	}
?>