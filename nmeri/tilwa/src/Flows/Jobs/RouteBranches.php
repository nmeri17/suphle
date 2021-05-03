<?php

	namespace Tilwa\Flows\Jobs;

	use Tilwa\App\ModuleToRoute;

	use Tilwa\Flows\Structures\{BranchesContext, RouteUserNode};

	use Tilwa\Flows\Previous\UnitNode;

	use Tilwa\Http\Response\ResponseManager;

	// for queueing the cached endpoint on hit and queuing sub-flows
	class RouteBranches {

		private $context;

		private $moduleFinder;

		private $hydrator;

		function __construct(BranchesContext $context) {
			
			$this->context = $context;
		}

		public function handle(ModuleToRoute $moduleFinder, FlowHydrator $hydrator) {

			$this->moduleFinder = $moduleFinder;

			$this->hydrator = $hydrator;

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

		// transitions from non-flow to flow links won't cache the first link if it's outside the active module i.e. routes in moduleA controllers can't visit those in moduleB if the moduleA route wasn't loaded from cache
		private function getManagerFromModules(array $modules, string $pattern):ResponseManager {

			$moduleInitializer = $this->moduleFinder->findContext($modules, $pattern);

			if (!is_null($moduleInitializer))

				return $moduleInitializer->getResponseManager();
		}
	}
?>