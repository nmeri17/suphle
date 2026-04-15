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

        protected readonly PathPlaceholders $placeholders,

        protected readonly RendererManager $rendererManager
    ) {}

    /**
     *
     * @throws ValidationFailure
    */
    public function handleFoundRoute(RouteInfo $route): BaseRenderer
    {
        $this->rendererManager->mayBeInvalid() // this should probably happen only after auth ie it should be a midw placed before the final one
        ->bootDefaultRenderer();

        /*$this->finalRenderer = $this->rendererManager->handleValidRequest(
            $this->container->getClass(PayloadStorage::class)
        );*/
        // Sync dynamic path variables (e.g., /user/{id})
        $this->placeholders->setSegmentValues($route->getAllParameters());

        $middlewareQueue = $this->container->whenType(MiddlewareQueue::class)
            ->needsArguments([
                "boundPreMidw" => $route->preMiddlewares,

                "boundMidw" => $route->middlewares
            ])->getClass(MiddlewareQueue::class);

        return $middlewareQueue->runStack();
    }
}