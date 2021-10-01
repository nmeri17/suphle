<?php
	namespace Tilwa\Tests\Integration\Controllers;

	use Tilwa\Testing\BaseTest;

	class ResponseManagerTest extends BaseTest {

		// the next 3 methods all trigger [bootControllerManager]
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
	}
?>