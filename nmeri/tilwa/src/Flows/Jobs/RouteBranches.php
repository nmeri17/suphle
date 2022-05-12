<?php
	namespace Tilwa\Flows\Jobs;

	use Tilwa\Modules\ModuleToRoute;

	use Tilwa\Flows\{FlowHydrator, Structures\BranchesContext, Previous\UnitNode};

	use Tilwa\Response\RoutedRendererManager;

	use Tilwa\Request\RequestDetails;

	use Tilwa\Contracts\Queues\Task;

	// for queueing the cached endpoint on hit and queuing sub-flows
	class RouteBranches implements Task {

		private $context, $moduleFinder, $hydrator;

		function __construct(BranchesContext $context, ModuleToRoute $moduleFinder, FlowHydrator $hydrator) {
			
			$this->context = $context;

			$this->moduleFinder = $moduleFinder;

			$this->hydrator = $hydrator;
		}

		public function handle ():void {

			$outgoingRenderer = $this->context->getRenderer();

			if (!$outgoingRenderer->hasBranches()) return;
			
			$outgoingRenderer->getFlow()

			->eachBranch(function ($urlPattern, $structure) {

				$manager = $this->findRendererManager($urlPattern);

				if (!$manager) return;

				$this->executeFlowBranch($manager, $urlPattern, $structure);
			});
		}

		private function findRendererManager (string $urlPattern):?RoutedRendererManager {

			$modules = $this->context->getModules();

			if (!is_null($modules))

				return $this->getManagerFromModules($modules, $urlPattern);

			return $this->context->getRoutedRendererManager();
		}

		/**
		 * Transitions from non-flow to flow links won't cache the first link if it's outside the active module i.e. routes in moduleA controllers can't visit those in moduleB if the moduleA route wasn't loaded from cache
		 * 
		 * Given the origin path stored a flow pointing to "sub-path/id", this tries to uproot the responseManager in the module containing that path
		*/
		private function getManagerFromModules(array $modules, string $pattern):?RoutedRendererManager {

			RequestDetails::fromModules($modules, $pattern);

			$moduleInitializer = $this->moduleFinder->findContext($modules);

			if (!is_null($moduleInitializer)) {

				$moduleInitializer->whenActive()->setRendererManager();

				return $moduleInitializer->getRoutedRendererManager();
			}

			return null;
		}

		private function executeFlowBranch (RoutedRendererManager $rendererManager, string $urlPattern, UnitNode $structure):void {

			$previousPayload = $this->context->getRenderer()->getRawResponse();

			$this->hydrator->setDependencies($rendererManager, $previousPayload, $urlPattern)
			
			->runNodes( $structure, $this->context->getUserId());
		}
	}
?>