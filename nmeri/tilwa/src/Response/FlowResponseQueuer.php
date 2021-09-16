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

			$user = $this->authStorage->getUser();

			$this->queueManager->push(RouteBranches::class,
				new BranchesContext(null, $user, $renderer, $responseManager )
			);
		}
	}
?>