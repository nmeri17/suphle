<?php

namespace Suphle\Response;

use Suphle\Contracts\{Auth\AuthStorage, Presentation\BaseRenderer};

use Suphle\Queues\AdapterManager;

use Suphle\Flows\{Jobs\RouteBranches, Structures\PendingFlowDetails};

use Suphle\Modules\Structures\ActiveDescriptors;

class FlowResponseQueuer
{
    public function __construct(
        protected readonly AdapterManager $queueManager,
        protected readonly AuthStorage $authStorage,
        protected readonly ActiveDescriptors $descriptorsHolder
    ) {

        //
    }

    public function saveSubBranches(BaseRenderer $renderer): void
    {

        $this->queueManager->addTask(RouteBranches::class, [

            PendingFlowDetails::class => new PendingFlowDetails(
                $renderer,
                $this->authStorage
            ),

            ActiveDescriptors::class => $this->descriptorsHolder
        ]);
    }
}
