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
        $pendingFlow = $this->container->whenType(PendingFlowDetails::class)->needsAny([

            BaseRenderer::class => $renderer,

            RouteInfo::class => $routeDetails,

            AuthStorage::class => $this->authStorage
        ])->getClass(PendingFlowDetails::class);

        $this->queueManager->addTask(RouteBranches::class, [

            PendingFlowDetails::class => $pendingFlow,

            ActiveDescriptors::class => $this->descriptorsHolder
        ]);
    }
}
