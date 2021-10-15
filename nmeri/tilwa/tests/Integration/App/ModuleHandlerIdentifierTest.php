<?php
	namespace Tilwa\Tests\Integration\App;

	use Tilwa\Testing\IsolatedComponentTest;

	class ModuleHandlerIdentifierTest extends IsolatedComponentTest {
		
		/**
		 * This functionality is tested in more detail in [BrowserAuthRepoTest]. Here, we are confirming it works as intended within the current context
		*/
		public function test_can_handle_login () {

			// confirm a response is gotten

			// orm needed here
		}

		public function test_generic_can_save_flow_and_get_its_response () {

			// store a flow link and confirm its response. then visit that our flow link and confirm its response
		}

		public function test_saved_flow_calls_flow_handler () {

			// use a trapper property like we did for events to confirm [flowRequestHandler] is called
		}
	}
?>