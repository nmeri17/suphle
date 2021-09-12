<?php

	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Testing\BaseTest;

	class ApiAuthRepoTest extends BaseTest { // note: if routing isn't working, recall this is/should be using a separate router
		// credentials are compared i.e wrong fails with array of message and correct returns token
		// on success, confirm that a revisit is identified as same user
		public function test_successLogin () {

			//
		}

		public function test_failedLogin () {

			//
		}

		public function test_logout () {

			// confirm that running this empties what was stored earlier i.e. two different requests ought to be made inside this and the login test
		}

		public function test_login_retention () {

			// Confirm that something can be found in session in-between requests
		}

		public function test_loginAs () {

			//
		}
	}
?>