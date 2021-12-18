<?php

	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Testing\IsolatedComponentTest;

	class BrowserAuthRepoTest extends IsolatedComponentTest {

		public function test_successLogin () {

			// an additional spec is confirming we are getting specific renderers back. First, try with the defaults at [BrowserLoginRenderer], then try swapping in ours
		}

		public function test_failedLogin () {

			//
		}

		public function test_get_user_on_unauth_route_yields_from_default_storage () {

			//
		}

		public function test_cant_access_api_auth_route_with_session () {

			//
		}
	}
?>