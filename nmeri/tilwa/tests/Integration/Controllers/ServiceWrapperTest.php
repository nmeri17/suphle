<?php

	namespace Tilwa\Tests\Integration\Controllers;

	use Tilwa\Testing\IsolatedComponentTest;

	class ServiceWrapperTest extends IsolatedComponentTest { // NOTE: these all go through the controller action method (after getting wrapped in the [load] method). Perhaps, a special IsolatedComponentTest will be needed

		public function test_emits_events_on_call() {

			// confirm the right payload gets to who we want it to get to when service is triggered and the config is turned on
		}

		public function test_emits_events_after_call() {

			// confirm the result is set by the time this guy's handler runs
		}

		public function test_successful_call_returns_value () {

			//
		}

		public function test_failed_call_returns_default_type () {

			//
		}
	}
?>