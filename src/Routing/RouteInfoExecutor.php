<?php
namespace Suphle\Routing;

use Suphle\Middleware\MiddlewareQueue;
use Suphle\Contracts\{Presentation\BaseRenderer, Response\RendererManager};
use Suphle\Routing\Structures\RouteInfo;
use Suphle\Hydration\Container;

class RouteInfoExecutor
{
    public function __construct(
        protected readonly Container $container,

        protected readonly RouteInfo $placeholders,

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

        $middlewareQueue = $this->container->whenType(MiddlewareQueue::class)
            ->needsArguments([
                "boundPreMidw" => $route->preMiddlewares,

                "boundMidw" => $route->middlewares
            ])->getClass(MiddlewareQueue::class);

        return $middlewareQueue->runStack();
    }
}