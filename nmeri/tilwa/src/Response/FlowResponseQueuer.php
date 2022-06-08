<?php
	namespace Tilwa\Response;

	use Tilwa\Contracts\{Auth\AuthStorage, Presentation\BaseRenderer};

	use Tilwa\Queues\AdapterManager;

	use Tilwa\Flows\{Jobs\RouteBranches, Structures\BranchesContext};

	class FlowResponseQueuer {

		private $queueManager, $authStorage;

		public function __construct (AdapterManager $queueManager, AuthStorage $authStorage) {

			$this->queueManager = $queueManager;

			$this->authStorage = $authStorage;
		}

		public function saveSubBranches (BaseRenderer $renderer):void {

			$this->queueManager->augmentArguments(RouteBranches::class, [
				
				"context" => new BranchesContext(
					$renderer,

					$this->authStorage->getUser()
				)
			]);
		}
	}
?>