<?php

	namespace Tilwa\Controllers;

	class ServiceWrapper { // needs the event manager and container

		public function __call() {
			# assumes the service is already loaded into memory. on call, we run call user func with the args in a try catch, then emit our events
			// we have on error, before call, after call, is setting value (can be used to replace service response)
			// after emitting the on error event, the return type of that interface method will be returned to the caller
		}

		private function yield() {
			# code...
		}

		private function failureReturnValue() {
			# code...
		}
	}
?>