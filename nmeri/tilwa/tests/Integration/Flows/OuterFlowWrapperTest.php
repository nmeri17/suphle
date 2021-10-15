<?php
	namespace Tilwa\Tests\Integration\Flows;

	use Tilwa\Testing\IsolatedComponentTest;

	class OuterFlowWrapperTest extends IsolatedComponentTest { // this should obviously be written after we've confirmed flows are functional
		
		public function test_empties_cache_entry_after_returning() {

			//
		}

		public function test_will_emitEvents_after_returning_flow_request() {

			// mount a listener to the flow triggered from here
		}
 
		public function test_will_queueBranches_after_returning_flow_request(){

			// will likely use the underlying queue wrapper
		}
 
		public function test_will_queueBranches_after_returning_organic_request_with_flow(){

			// SUT ==> ResponseManager->afterRender
		}
	}
?>