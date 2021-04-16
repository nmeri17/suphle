<?php

	namespace Tilwa\Flows\Jobs;

	use Tilwa\App\ModuleToRoute;

	use Tilwa\Flows\Structures\BranchesContext;

	// for queueing the cached endpoint on hit
	class RouteBranches {

		private $context;

		function __construct(BranchesContext $context) { // needs an authentication object
			
			$this->context = $context;
		}
// find where route branches was created and inject a renderer into our context. use [getOutgoingPath]
		public function handle(ModuleToRoute $moduleFinder, FlowHydrator $hydrator, EventManager $eventManager) {

			$renderer = $this->context->getRenderer();

			if ($renderer->hasBranches())
			
				$this->handleSubFlows($renderer, $hydrator, $eventManager);
		}

		private function handleSubFlows(AbstractRenderer $renderer, FlowHydrator $hydrator, EventManager $eventManager) {

			$this->context->setEventManager($eventManager); // note: context here is flowContext not our guy

			$hydrator->setContext($this->context)->runNodes();

			// for each branch

			$moduleInitializer = $moduleFinder->findContext(

				$context->getModules(), $pattern// when module is absent, borrow the previous guy's modules since it won't possibly be updated at runtime. but state in the docs that transitioning from non-flow to flow route will result in working with stale modules
			);

			$renderer = $moduleInitializer->getRouter()->getActiveRenderer();
		}
// formerly on abstract renderer. copy user id and flowContext creation
		public function queueNextFlow():bool { // this should be injected by whoever triggers the queue

			$id = $user ? strval($user->id) ? "*";

			$this->queueManager->push(RouteQueue::class, 

				new FlowContext($id, $this->rawResponse, $this->flows) // push all this route queue handler into route branches
			);
		}
	}
?>