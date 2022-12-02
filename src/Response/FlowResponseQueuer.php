<?php
	namespace Suphle\Response;

	use Suphle\Contracts\{Auth\AuthStorage, Presentation\BaseRenderer};

	use Suphle\Queues\AdapterManager;

	use Suphle\Flows\{Jobs\RouteBranches, Structures\PendingFlowDetails};

	class FlowResponseQueuer {

		public function __construct (
			private readonly AdapterManager $queueManager,

			private readonly AuthStorage $authStorage
		) {

			//
		}

		public function saveSubBranches (BaseRenderer $renderer):void {

			$this->queueManager->addTask(RouteBranches::class, [
				
				"flowDetails" => new PendingFlowDetails(
					
					$renderer, $this->authStorage
				)
			]);
		}
	}
?>