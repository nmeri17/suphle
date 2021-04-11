<?php

	namespace Tilwa\Flows\Jobs;

	use Tilwa\App\ModuleToRoute;

	// for queueing the cached endpoint on hit
	class RouteBranches {

		private $context;

		function __construct(BranchesContext $context) {
			
			$this->context = $context;
		}

		public function handle(ModuleToRoute $moduleFinder) {

			$context = $this->context;

			$moduleInitializer = $moduleFinder->findContext(

				$context->getModules(), $context->getOutgoingPath()
			);

			$renderer = $moduleInitializer->getRouter()->getActiveRenderer();

			if ($renderer->hasBranches())
			
				$renderer->queueNextFlow();
		}
	}
?>