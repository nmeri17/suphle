<?php
	namespace Tilwa\Tests\Integration\App;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	class ModuleInitializerTest extends IsolatedComponentTest {
		
		public function test_session_resumption() {

			// stub responseManager::requestAuthenticationStatus and confirm ::attemptAuthentication throws errors or doesn't
		}

		public function test_stranger_cant_get_creators_resource () {

			// should throw [Unauthenticated]
		}

		public function test_authorizeRequest() {

			//
		}

		public function test_runStack () {

			// we wanna visit a route containing some middleware, then confirm the underlying middleware were triggered

			// a starting point may be verifying if it got to the middleware collector
		}
	}
?>