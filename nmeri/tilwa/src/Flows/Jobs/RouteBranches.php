<?php
	namespace Tilwa\Flows\Jobs;

	use Tilwa\Modules\{ModuleToRoute, ModulesBooter};

	use Tilwa\Flows\{FlowHydrator, Structures\BranchesContext, Previous\UnitNode};

	use Tilwa\Response\RoutedRendererManager;

	use Tilwa\Request\RequestDetails;

	use Tilwa\Contracts\{Queues\Task, Database\OrmDialect};

	// for queueing the cached endpoint on hit and queuing sub-flows
	class RouteBranches implements Task {

		private $context, $moduleFinder, $hydrator, $modules;

		public function __construct(
			BranchesContext $context, ModuleToRoute $moduleFinder,

			FlowHydrator $hydrator, ModulesBooter $modulesBooter
		) {
			
			$this->context = $context;

			$this->moduleFinder = $moduleFinder;

			$this->hydrator = $hydrator;

			$this->modules = $modulesBooter->getModules();
		}

		public function handle ():void {

			$outgoingRenderer = $this->context->getRenderer();

			if (!$outgoingRenderer->hasBranches()) return;
			
			$outgoingRenderer->getFlow()

			->eachBranch(function ($urlPattern, $structure) {

				$manager = $this->findManagerForPattern( $urlPattern);

				if (!$manager) return; // invalid url

				$this->executeFlowBranch($manager, $urlPattern, $structure);
			});
		}

		/**
		 * Given the origin path stored a flow pointing to "sub-path/id", this tries to uproot the responseManager in the module containing that path
		*/
		private function findManagerForPattern (string $pattern):?RoutedRendererManager {

			RequestDetails::fromModules($this->modules, $pattern);

			$moduleInitializer = $this->moduleFinder->findContext($this->modules);

			if (!is_null($moduleInitializer)) {

				$moduleInitializer->whenActive()->setRendererManager();

				return $moduleInitializer->getRoutedRendererManager();
			}

			$this->restoreConnections();

			return null;
		}

		/**
		 * Undo connection resetting action so underlying ORM client's subsequent calls aren't disrupted by trying to hydrate a blank connection instance
		*/
		private function restoreConnections ():void {

			foreach ($this->modules as $descriptor)

				$descriptor->getContainer()->getClass(OrmDialect::class);
		}

		private function executeFlowBranch (RoutedRendererManager $rendererManager, string $urlPattern, UnitNode $structure):void {

			$previousPayload = $this->context->getRenderer()->getRawResponse();

			$this->hydrator->setDependencies($rendererManager, $previousPayload, $urlPattern)
			
			->runNodes( $structure, $this->context->getUserId());
		}
	}
?>