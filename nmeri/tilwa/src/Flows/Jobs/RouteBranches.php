<?php
	namespace Tilwa\Flows\Jobs;

	use Tilwa\Modules\{ModuleToRoute, ModulesBooter};

	use Tilwa\Flows\{FlowHydrator, Structures\PendingFlowDetails, Previous\UnitNode};

	use Tilwa\Response\RoutedRendererManager;

	use Tilwa\Request\RequestDetails;

	use Tilwa\Routing\PathPlaceholders;

	use Tilwa\Contracts\{Queues\Task, Database\OrmDialect};

	class RouteBranches implements Task {

		private $flowDetails, $moduleFinder, $hydrator, $modulesBooter,

		$modules;

		public function __construct(
			PendingFlowDetails $flowDetails, ModuleToRoute $moduleFinder,

			FlowHydrator $hydrator, ModulesBooter $modulesBooter
		) {
			
			$this->flowDetails = $flowDetails;

			$this->moduleFinder = $moduleFinder;

			$this->hydrator = $hydrator;

			$this->modulesBooter = $modulesBooter;
		}

		public function handle ():void {

			$outgoingRenderer = $this->flowDetails->getRenderer();

			if (!$outgoingRenderer->hasBranches()) return;

			$this->modules = $this->modulesBooter->bootAllModules()

			->prepareAllModules()->getModules();
			
			$outgoingRenderer->getFlow()

			->eachBranch(function ($urlPattern, $structure) {

				if (!$this->findManagerForPattern( $urlPattern)) return; // invalid url

				$this->executeFlowBranch($urlPattern, $structure);
			});
		}

		/**
		 * Given the origin path stored a flow pointing to "sub-path/id", this tries to uproot the responseManager in the module containing that path
		*/
		private function findManagerForPattern (string $pattern):bool {

			RequestDetails::fromModules($this->modules, $pattern);

			$moduleInitializer = $this->moduleFinder->findContext($this->modules);

			if (!is_null($moduleInitializer)) {

				$moduleInitializer->whenActive();

				return true;
			}

			$this->restoreConnections();

			return false;
		}

		/**
		 * Undo connection resetting action so underlying ORM client's subsequent calls aren't disrupted by trying to hydrate a blank connection instance
		*/
		private function restoreConnections ():void {

			foreach ($this->modules as $descriptor)

				$descriptor->getContainer()->getClass(OrmDialect::class);
		}

		private function executeFlowBranch ( string $urlPattern, UnitNode $structure):void {

			$previousPayload = $this->flowDetails->getRenderer()->getRawResponse();

			$container = $this->moduleFinder->getActiveModule()

			->getContainer();

			$rendererManager = $container->getClass(RoutedRendererManager::class);

			$placeholderStorage = $container->getClass(PathPlaceholders::class);

			$this->hydrator->setDependencies(
				$rendererManager, $placeholderStorage,

				$previousPayload, $urlPattern
			)
			->runNodes( $structure, $this->flowDetails->getUserId());
		}
	}
?>