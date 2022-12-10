<?php
	namespace Suphle\Flows\Jobs;

	use Suphle\Modules\{ModuleToRoute, ModulesBooter, Structures\ActiveDescriptors};

	use Suphle\Flows\{FlowHydrator, Structures\PendingFlowDetails, Previous\UnitNode};

	use Suphle\Request\RequestDetails;

	use Suphle\Services\DecoratorHandlers\VariableDependenciesHandler;

	use Suphle\Contracts\{Queues\Task, Database\OrmDialect, IO\CacheManager};

	class RouteBranches implements Task {

		final public const FLOW_MECHANISMS = "flow_wildcards";

		private array $modules;

		private bool $wildcardNotExist = false;

		public function __construct(
			private readonly PendingFlowDetails $flowDetails,

			private readonly ModuleToRoute $moduleFinder,

			private readonly FlowHydrator $hydrator,

			private readonly ModulesBooter $modulesBooter,

			private readonly ActiveDescriptors $descriptorsHolder,

			private readonly CacheManager $cacheManager
		) {
			
			//
		}

		public function handle ():void {

			$outgoingRenderer = $this->flowDetails->getRenderer();

			if (!$outgoingRenderer->hasBranches()) return;

			$this->modules = $this->descriptorsHolder->getOriginalDescriptors();

			$this->modulesBooter->bootAllModules($this->descriptorsHolder)

			->prepareAllModules();
			
			$outgoingRenderer->getFlow()

			->eachBranch(function ($urlPattern, $structure) {

				$mechanismPath = $this->getMechanismPath($urlPattern);

				if (!$this->patternMatchesMechanism($mechanismPath))

					return;

				elseif ($this->wildcardNotExist)

					$this->setMechanismPath($mechanismPath);

				if (!$this->findManagerForPattern( $urlPattern)) return; // invalid url

				$this->executeFlowBranch($urlPattern, $structure);
			});
		}

		// Each pattern can only have one mechanism. If it's saved without this consideration, attempting to read it later on will fail
		protected function patternMatchesMechanism (string $mechanismPath):bool {

			$patternMechanism = $this->cacheManager->getItem($mechanismPath);

			if (is_null($patternMechanism))

				return $this->wildcardNotExist = true;

			return $patternMechanism == $this->flowDetails->getAuthStorage();
		}

		protected function getMechanismPath (string $urlPattern):string {

			return self::FLOW_MECHANISMS . "/" . trim($urlPattern, "/");
		}

		protected function setMechanismPath (string $mechanismPath):void {

			$this->cacheManager->saveItem(

				$mechanismPath, $this->flowDetails->getAuthStorage()
			);
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

			$previousPayload = $this->flowDetails->getRenderer()

			->getRawResponse();

			$this->hydrator->setRequestDetails(

				$previousPayload, $urlPattern
			);

			$this->setHydratorDependencies();

			$this->hydrator->runNodes( $structure, $this->flowDetails);
		}

		private function setHydratorDependencies ():void {

			$handler = $this->moduleFinder->getActiveModule()

			->getContainer()->getClass(VariableDependenciesHandler::class);

			foreach ($this->hydrator->dependencyMethods() as $methodName)

				$handler->executeDependencyMethod($methodName, $this->hydrator);
		}
	}
?>