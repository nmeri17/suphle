<?php
	namespace Tilwa\Response;

	use Tilwa\Response\Format\AbstractRenderer;

	use Tilwa\Contracts\Auth\AuthStorage;

	use Tilwa\Queues\AdapterManager;

	use Tilwa\Flows\Jobs\RouteBranches;

	class FlowResponseQueuer {

		private $queueManager, $authStorage;

		public function __construct (AdapterManager $queueManager, AuthStorage $authStorage) {

			$this->queueManager = $queueManager;

			$this->authStorage = $authStorage;
		}

		public function insert (AbstractRenderer $renderer, ResponseManager $responseManager):void {

			$this->queueManager->augmentArguments(RouteBranches::class, [
				new BranchesContext(
					$renderer,

					$this->authStorage->getUser(),

					null, $responseManager
				)
			]);
		}
	}
?>