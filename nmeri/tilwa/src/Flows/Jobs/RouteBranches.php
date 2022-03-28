<?php
	namespace Tilwa\Flows\Jobs;

	use Tilwa\Modules\ModuleToRoute;

	use Tilwa\Flows\FlowHydrator;

	use Tilwa\Flows\Structures\{BranchesContext, RouteUserNode};

	use Tilwa\Flows\Previous\UnitNode;

	use Tilwa\Response\ResponseManager;

	use Tilwa\Contracts\Queues\Task;

	// for queueing the cached endpoint on hit and queuing sub-flows
	class RouteBranches implements Task {

		private $context, $moduleFinder, $hydrator;

		function __construct(BranchesContext $context, ModuleToRoute $moduleFinder, FlowHydrator $hydrator) {
			
			$this->context = $context;

			$this->moduleFinder = $moduleFinder;

			$this->hydrator = $hydrator;
		}

		public function handle() {

			$outgoingRenderer = $this->context->getRenderer();

			if ($outgoingRenderer->hasBranches())
			
				$outgoingRenderer->getFlow()->eachBranch($this->eachFlowBranch);
		}

		private function eachFlowBranch(string $urlPattern, UnitNode $structure) {

			$context = $this->context;

			$modules = $context->getModules();

			if (!is_null($modules))

				$manager = $this->getManagerFromModules($modules, $urlPattern);

			else $manager = $context->getResponseManager();

			if ($manager) {
				
				$previousPayload = $context->getRenderer()->getRawResponse();

				$this->hydrator->setDependencies($manager, $previousPayload)
				
				->runNodes( $structure, $context->getUserId());
			}
		}

		/**
		 * Transitions from non-flow to flow links won't cache the first link if it's outside the active module i.e. routes in moduleA controllers can't visit those in moduleB if the moduleA route wasn't loaded from cache
		 * 
		 * Given the origin path stored a flow pointing to "sub-path/id", this tries to uproot the responseManager in the module containing that path
		*/
		private function getManagerFromModules(array $modules, string $pattern):?ResponseManager {

			$moduleInitializer = $this->moduleFinder->findContext($modules, $pattern);

			if (!is_null($moduleInitializer))

				return $moduleInitializer->getResponseManager();
		}
	}
?>