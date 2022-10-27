<?php
	namespace Suphle\Flows\Jobs;

	use Suphle\Modules\{ModuleToRoute, ModulesBooter};

	use Suphle\Flows\{FlowHydrator, Structures\PendingFlowDetails, Previous\UnitNode};

	use Suphle\Response\RoutedRendererManager;

	use Suphle\Request\RequestDetails;

	use Suphle\Routing\PathPlaceholders;

	use Suphle\Contracts\{Queues\Task, Database\OrmDialect};

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

			current($this->modules)->getContainer()

			->getClass(OrmDialect::class)

			->restoreConnections($this->modules);

			return false;
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
			->runNodes( $structure, $this->flowDetails);
		}
	}
?>