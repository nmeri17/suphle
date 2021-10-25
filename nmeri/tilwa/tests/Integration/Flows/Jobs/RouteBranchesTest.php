<?php

	namespace Tilwa\Flows\Jobs;

	use Tilwa\Testing\IsolatedComponentTest;

	class RouteBranchesTest extends IsolatedComponentTest { /*
		- inject the [BranchesContext] containing one of the flow states into the constructor, then rig up parameters for the `handle` method from the laravel container
	*/

		public function test_handle() {

			/*	Given:
					- the renderer we're pulling from the context should contain some structure/form of response for this guy to work with
					- `context` can be in 2 module states. Check SUT->eachFlowBranch
				When:
					- call this guy
				Then:
					- find flow in the context matching what is saved in a module
					- it contains an internal call to $this->cacheManager->save($urlPattern, $umbrella); We want to make sure [urlPattern] matches something intended, and that [umbrella] carries a pointer to requester (could be currently logged in user or random)
					- both should visit one flow. Confirm they can view the same content when assigned to both and are restricted when set to one user
			*/
		}
	}
?>