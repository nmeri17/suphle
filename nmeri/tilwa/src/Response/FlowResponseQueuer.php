<?php

	namespace Tilwa\Response;

	use Tilwa\Response\Format\AbstractRenderer;

	use Tilwa\Contracts\{QueueManager, Authenticator};

	use Tilwa\Flows\Jobs\RouteBranches;

	class FlowResponseQueuer {

		private $queueManager, $authenticator;

		public function __construct (QueueManager $queueManager, Authenticator $authenticator) {

			$this->queueManager = $queueManager;

			$this->authenticator = $authenticator;
		}

		public function insert (AbstractRenderer $renderer, ResponseManager $responseManager):void {

			$user = $this->authenticator->getUser();

			$this->queueManager->push(RouteBranches::class,
				new BranchesContext(null, $user, $renderer, $responseManager )
			);
		}
	}
?>