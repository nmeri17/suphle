<?php

namespace Suphle\Flows;

use Suphle\Flows\Jobs\{RouteBranches, UpdateCountDelete};

use Suphle\Flows\Structures\{RouteUserNode, AccessContext, PendingFlowDetails, RouteUmbrella};

use Suphle\Contracts\{Events, Response\BaseResponseManager, IO\CacheManager, Auth\AuthStorage, Modules\HighLevelRequestHandler, Presentation\BaseRenderer};

use Suphle\Queues\AdapterManager;

use Suphle\Request\RequestDetails;

use Suphle\Hydration\Container;

use Suphle\Modules\Structures\ActiveDescriptors;

class OuterFlowWrapper implements BaseResponseManager, HighLevelRequestHandler
{
    final public const ALL_USERS = "*";

    protected ?RouteUmbrella $routeUmbrella = null;

    protected string $activeUser;

    protected ?RouteUserNode $routeUserNode = null;

    protected AuthStorage $authStorage;

    public function __construct(
        protected readonly RequestDetails $requestDetails,
        protected readonly AdapterManager $queueManager,
        protected readonly UmbrellaSaver $flowSaver,
        protected readonly Container $container,
        protected readonly Events $eventManager,
        protected readonly ActiveDescriptors $descriptorsHolder
    ) {

        //
    }

    public function canHandle(): bool
    {

        $this->routeUmbrella = $this->flowSaver->getExistingUmbrella($this->dataPath());

        if (is_null($this->routeUmbrella)) {
            return false;
        }

        $this->setAuthFromStored();

        $this->routeUserNode = $this->getVisitorsFlow($this->getVisitingUserId());

        $userHasContent = !is_null($this->routeUserNode);

        if (!$userHasContent) {

            $this->container->refreshClass(AuthStorage::class);
        }

        return $userHasContent;
    }

    protected function setAuthFromStored(): void
    {

        $this->authStorage = $this->container->getClass(
            $this->routeUmbrella->getAuthStorage()
        );

        $this->container->whenTypeAny()->needsAny([

            AuthStorage::class => $this->authStorage
        ]);
    }

    private function getVisitorsFlow(string $visitorId): ?RouteUserNode
    {

        $userPayload = $this->routeUmbrella->getUserPayload($visitorId);

        if (is_null($userPayload) && ($visitorId != self::ALL_USERS)) { // assume data was saved for general user base

            $visitorId = self::ALL_USERS;

            $userPayload = $this->routeUmbrella->getUserPayload($visitorId);
        }

        $this->activeUser = $visitorId;

        return $userPayload;
    }

    private function getVisitingUserId(): string
    {

        $user = $this->authStorage->getUser();

        return is_null($user) ? self::ALL_USERS : strval($user->getId());
    }

    public function afterRender($cachedResponse = null): void
    {

        $this->emitEvents($cachedResponse);

        $this->queueBranches();
    }

    public function emptyFlow(): void
    {

        $this->queueManager->addTask(UpdateCountDelete::class, [
            "theAccessed" => new AccessContext(
                $this->dataPath(),
                $this->routeUserNode,
                $this->routeUmbrella,
                $this->activeUser
            )
        ]);
    }

    private function dataPath(): string
    {

        return $this->flowSaver->getPatternLocation(
            $this->requestDetails->getPath()
        );
    }

    private function emitEvents($cachedResponse): void
    {

        $renderer = $this->responseRenderer();

        $this->eventManager->emit(
            $renderer->getCoordinator()::class,
            $renderer->getHandler(),
            $cachedResponse // event handler can then inject payloadStorage/pathPlaceholders
        );
    }

    private function queueBranches(): void
    {

        $this->queueManager->addTask(RouteBranches::class, [

            PendingFlowDetails::class => new PendingFlowDetails(
                $this->responseRenderer(),
                $this->authStorage
            ),

            ActiveDescriptors::class => $this->descriptorsHolder
        ]);
    }

    public function responseRenderer(): BaseRenderer
    {

        return $this->routeUserNode->getRenderer();
    }

    public function handlingRenderer(): ?BaseRenderer
    {

        return $this->responseRenderer();
    }
}
