<?php
namespace Suphle\Routing;

use Suphle\Contracts\{Presentation\BaseRenderer, Response\RendererManager, Routing\MiddlewareRegistry};
use Suphle\Routing\Structures\RouteInfo;
use Suphle\Hydration\Container;

class RouteInfoExecutor
{
    public function __construct(
        protected readonly Container $container,

        protected readonly RendererManager $rendererManager
    ) {}

    /**
     *
     * @throws ValidationFailure
    */
    public function handleFoundRoute(RouteInfo $route): BaseRenderer
    {
        $this->container->whenTypeAny()->needsAny([RouteInfo::class => $route]);

        $this->rendererManager->mayBeInvalid($route) // this should probably happen only after auth ie it should be a midw placed before the final one
        ->bootDefaultRenderer();

        return $this->container->getClass(MiddlewareRegistry::class)
        ->setRawHandlers($route->preMiddlewares, $route->middlewares)
        ->runStack();
    }
}