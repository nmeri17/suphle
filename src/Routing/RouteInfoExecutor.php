<?php
namespace Suphle\Routing;

use Suphle\Contracts\{Presentation\BaseRenderer, Routing\MiddlewareRegistry};
use Suphle\Routing\Structures\RouteInfo;
use Suphle\Request\RequestDetails;

class RouteInfoExecutor
{
    public function __construct(
        protected readonly RequestDetails $requestDetails,
        protected readonly MiddlewareRegistry $middlewareRegistry
    ) {}

    /**
     *
     * @throws ValidationFailure
    */
    public function handleFoundRoute(RouteInfo $routeDetails): BaseRenderer
    {
        $this->requestDetails->setRouteInfo($routeDetails);

        return $this->middlewareRegistry
        ->runStack($routeDetails->preMiddlewares, $routeDetails->middlewares);
    }
}