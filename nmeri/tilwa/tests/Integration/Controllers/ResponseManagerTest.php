<?php
	namespace Tilwa\Tests\Integration\Controllers;

	use Tilwa\Testing\IsolatedComponentTest;

	/* I think these should be unit tests sha. Call the high level methods here */
	class ResponseManagerTest extends IsolatedComponentTest {

		// the next 3 methods all trigger [bootControllerManager]
		// one of these guys has to make a POST request to confirm underlying controller request validator is called and behaves correctly. [ValidatorController] is already created
		public function test_validateController () {

			// confirm it throws those errors when unsatisfactory
		}

		public function test_assignModelsInAction() {

			// in the then, call [hydrateModels]. Confirm it only works for post method. Lastly, check the effects on `handlerParameters`

			// also confirm method parameter matches what's in the request placeholder list
		}

		public function test_handleValidRequest () {

			// confirm action method's contents are returned
		}

		public function test_isValidRequest () {

			// confirm can find its way to its validator and returns true and false where applicable
		}

		public function test_failed_request_validation_reverts_renderer () {
			
			// 
		}
	}
?>