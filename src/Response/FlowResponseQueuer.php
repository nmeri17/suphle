<?php
	namespace Suphle\Response;

	use Suphle\Contracts\{Auth\AuthStorage, Presentation\BaseRenderer};

	use Suphle\Queues\AdapterManager;

	use Suphle\Flows\{Jobs\RouteBranches, Structures\PendingFlowDetails};

	use Suphle\Modules\Structures\ActiveDescriptors;

	class FlowResponseQueuer {

		public function __construct (
			private readonly AdapterManager $queueManager,

			private readonly AuthStorage $authStorage,

			private readonly ActiveDescriptors $descriptorsHolder
		) {

			//
		}

		public function saveSubBranches (BaseRenderer $renderer):void {

			$this->queueManager->addTask(RouteBranches::class, [
				
				PendingFlowDetails::class => new PendingFlowDetails(
					
					$renderer, $this->authStorage
				),

				ActiveDescriptors::class => $this->descriptorsHolder
			]);
		}
	}
?>