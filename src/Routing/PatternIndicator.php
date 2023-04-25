<?php

namespace Suphle\Routing;

use Suphle\Contracts\{Routing\RouteCollection, Auth\AuthStorage, Config\Router as RouterConfig};

use Suphle\Request\RequestDetails;

use Suphle\Routing\PreMiddlewareRegistry;

use Suphle\Middleware\MiddlewareRegistry;

class PatternIndicator
{
    protected ?bool $mirrorState = null;

    public function __construct(
        protected readonly MiddlewareRegistry $middlewareRegistry,
        protected readonly PreMiddlewareRegistry $preRegistry,
        protected readonly RouterConfig $routerConfig,
        protected readonly RequestDetails $requestDetails
    ) {

        //
    }

    public function logPatternDetails(RouteCollection $collection, string $pattern): void
    {

        $this->includeMiddleware($collection, $pattern);

        $this->updateMeta($collection, $pattern);
    }

    public function shouldMirror(): bool
    {

        if (is_null($this->mirrorState)) {

            $this->mirrorState = $this->requestDetails->isApiRoute() &&

            $this->routerConfig->mirrorsCollections();
        }

        return $this->mirrorState;
    }

    protected function includeMiddleware(RouteCollection $collection, string $segment): void
    {

        $collection->_assignMiddleware($this->middlewareRegistry);

        $this->middlewareRegistry->updateInteractedPatterns($segment);
    }

    protected function updateMeta(RouteCollection $collection, string $segment): void
    {

        $collection->_preMiddleware($this->preRegistry);

        $this->preRegistry->updateInteractedPatterns($segment);
    }

    /**
     * When a module has more than one route collection, the preceding collection could have logged to its registry. Without a reseet, those tags will [undesirably] affect the next collection handling routing
    */
    public function resetIndications(): void
    {

        $this->middlewareRegistry->emptyAllStacks();

        $this->preRegistry->emptyAllStacks();
    }
}
