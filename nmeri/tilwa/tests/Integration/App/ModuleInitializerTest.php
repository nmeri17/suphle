<?php
	namespace Tilwa\Tests\Integration\App;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	class ModuleInitializerTest extends IsolatedComponentTest { // SUT for the next 2 methods is [triggerRequest]
		
		public function test_session_resumption() {

			// user with x token/session does y thing on request to a protected route z; makes a 2nd request to route A expecting to find y again

			// requires login to get token and stuff
			
			// we want to call [attemptAuthentication] but the routes need to be ready or something. We can either upgrade this to [ModuleAssembly] or mock out all those dependencies (the stuff done by the router to activate the auth state for that route)
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