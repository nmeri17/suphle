<?php
namespace Suphle\Routing;

use Suphle\Contracts\{Presentation\BaseRenderer, Routing\MiddlewareRegistry};
use Suphle\Routing\Structures\RouteInfo;
use Suphle\Hydration\Container;

class RouteInfoExecutor
{
    public function __construct(
        protected readonly Container $container,
        protected readonly MiddlewareRegistry $middlewareRegistry
    ) {}

    /**
     *
     * @throws ValidationFailure
    */
    public function handleFoundRoute(RouteInfo $route): BaseRenderer
    {
        $this->container->whenTypeAny()->needsAny([RouteInfo::class => $route]);

        return $this->middlewareRegistry->runStack($route->preMiddlewares, $route->middlewares);
    }
}