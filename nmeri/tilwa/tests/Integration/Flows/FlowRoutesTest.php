<?php
	namespace Tilwa\Tests\Integration\Flows;

	use Tilwa\Testing\IsolatedComponentTest;

	class FlowRoutesTest extends IsolatedComponentTest {
		
		public function test_flow_response_matches_organic_response () {

			// Assert that the result of an organic call to one endpoint matches the flow request to the same endpoint. Note: this needs 2 requests -- 1 to the storer, and 2 to the flow url
		}
		
		public function test_only_specialized_user_can_access_his_content () {

			// Confirm they can view the same content when assigned to both and are restricted when set to one user
		}
		
		public function test_all_can_access_generalized_content () {

			//
		}
		
		public function test_pushed_to_flow () {

			// get organic through front door and confirm it pushed our job to the queue {assertPushedToFlow(name)}
		}
	}
?>