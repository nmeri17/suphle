<?php

	namespace Tilwa\Tests\Integration\App;

	use Tilwa\Testing\BaseTest;

	class ModuleInitializerTest extends BaseTest { // SUT for the next 2 methods is [triggerRequest]
		
		public function test_attemptAuthentication() {
			
			// we want to call SUT but the routes need to be ready or something. We can either upgrade this to [ModuleAssembly] or mock out all those dependecies (the stuff done by the router to activate the auth state for that route)
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