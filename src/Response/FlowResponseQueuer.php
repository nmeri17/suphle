<?php

namespace Suphle\Response;

use Suphle\Contracts\{Auth\AuthStorage, Presentation\BaseRenderer};

use Suphle\Routing\Structures\RouteInfo;

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

    public function saveSubBranches(BaseRenderer $renderer, RouteInfo $routeDetails): void
    {

        $this->queueManager->addTask(RouteBranches::class, [

            PendingFlowDetails::class => new PendingFlowDetails(
                $renderer, $routeDetails
                $this->authStorage
            ),

            ActiveDescriptors::class => $this->descriptorsHolder
        ]);
    }
}
