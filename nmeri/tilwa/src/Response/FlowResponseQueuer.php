<?php
	namespace Tilwa\Response;

	use Tilwa\Response\Format\AbstractRenderer;

	use Tilwa\Contracts\{QueueManager, Auth\AuthStorage};

	use Tilwa\Flows\Jobs\RouteBranches;

	class FlowResponseQueuer {

		private $queueManager, $authStorage;

		public function __construct (QueueManager $queueManager, AuthStorage $authStorage) {

			$this->queueManager = $queueManager;

			$this->authStorage = $authStorage;
		}

		public function insert (AbstractRenderer $renderer, ResponseManager $responseManager):void {

			$this->queueManager->addJob(RouteBranches::class, 

				$this->queueManager->augmentArguments([
					new BranchesContext(
						$renderer,

						$this->authStorage->getUser(),

						null, $responseManager
					)
				])
			);
		}
	}
?>