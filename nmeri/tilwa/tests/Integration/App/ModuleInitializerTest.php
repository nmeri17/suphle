<?php

	namespace Tilwa\Tests\Integration\App;

	use Tilwa\Testing\BaseTest;

	class ModuleInitializerTest extends BaseTest {
		
		public function test_attemptAuthentication() {
			
			// we want to call SUT->triggerRequest(), but the routes needs to be ready or something. We can either upgrade this to [ModuleAssembly] or mock out all those dependecies (the stuff done by the router to activate the auth state for that route)
		}

		public function test_authorizeRequest() {

			//
		}
	}
?>