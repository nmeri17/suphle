<?php

	namespace Tilwa\Controllers;

	class InterceptQueryService { // needs the event manager and container

		public function __call() {
			# assumes the service is already loaded into memory. on call, we run call user func with the args in a try catch, then emit our events
			// we have on error, before call, after call, is setting value (can be used to replace service response)
			// after emitting the on error event, the return type of that interface method will be returned to the caller
		}

		private function yield() {
			# prob does the calling. but has a list of interjections to be made both before and afterward
			// before => if the call can be made, insert the conditions into orm's listener
			/* afterward:
				- sub classes of InterceptsQuery shouldn't return any response if no database call was made i.e. nothing was intercepted
				- emit "fetched" event with the trapped arguments from InterceptsQuery
			*/
		}

		private function failureReturnValue() {
			# code...
		}
	}
?>