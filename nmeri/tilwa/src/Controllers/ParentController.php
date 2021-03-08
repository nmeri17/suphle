<?php

	namespace Tilwa\Controllers;

	class ParentController {

		public function alternateWhen() {
			# might need to be abstracted somewhere logic level services can equally use it
		}

		public function decide() {
			# flushes the above chain
		}

		public function loadServices(array $dependencies) {
			# code...
		}

		// this 2 run after controller initialization
		// restrict everything that goes into the services method to InterceptsQuery, exports, event manager
		private function isAcceptableService() {
			# code...
		}

		// checks if there are any new properties that weren't assigned by our services method
		private function hasIsolatedConstructor():bool {
			# code...
		}

		public function __get() {
			# used to access our magically assigned services. should either return some builder/wrapper or do the wrapping here
			// note: they should be lazy loaded instead of in the services method
		}
	}
?>