<?php

	namespace Tilwa\Flows\Jobs;

	use Tilwa\App\ModuleToRoute;

	use Tilwa\Flows\Structures\{BranchesContext, RouteUserNode};

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
			
				$this->handleSubFlows($outgoingRenderer);
		}

		private function handleSubFlows(AbstractRenderer $renderer) {

			$flowController = $renderer->getFlow();

			$flowController->setPreviousPayload($renderer->getRawResponse())

			->eachBranch($this->eachFlowBranch);
		}

		private function eachFlowBranch($urlPattern, $structure) {

			$context = $this->context;

			$modules = $context->getModules();

			if (!is_null($modules))

				$renderer = $this->getRendererFromModules($modules, $urlPattern);

			else $renderer = $context->getRouter()->findRenderer();

			if ($renderer)

				$this->hydrator->runNodes($renderer, $structure, $context->getUserId());
		}

		// if the queue can pick app index file, we can just pull its modules. otherwise, it means transitions from non-flow to flow links won't cache the first link if it's outside the active module i.e. routes in moduleA controllers can't visit those in moduleB if the moduleA route wasn't loaded from cache
		private function getRendererFromModules(array $modules, string $pattern):AbstractRenderer {

			$moduleInitializer = $this->moduleFinder->findContext($modules, $pattern);

			return $moduleInitializer->getRouter()->getActiveRenderer();
		}
	}
?>