<?php
	namespace Suphle\Response;

	use Suphle\Contracts\{Auth\AuthStorage, Presentation\BaseRenderer};

	use Suphle\Queues\AdapterManager;

	use Suphle\Flows\{Jobs\RouteBranches, Structures\PendingFlowDetails};

	class FlowResponseQueuer {

		private $queueManager, $authStorage;

		public function __construct (AdapterManager $queueManager, AuthStorage $authStorage) {

			$this->queueManager = $queueManager;

			$this->authStorage = $authStorage;
		}

		public function saveSubBranches (BaseRenderer $renderer):void {

			$this->queueManager->augmentArguments(RouteBranches::class, [
				
				"flowDetails" => new PendingFlowDetails(
					$renderer,

					$this->authStorage->getUser()
				)
			]);
		}
	}
?>