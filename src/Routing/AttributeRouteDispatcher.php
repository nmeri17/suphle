<?php

namespace Suphle\Routing;

use Suphle\Contracts\Routing\RouteDispatcher;
use Suphle\Contracts\Presentation\BaseRenderer;
use Suphle\Routing\Structures\RouteInfo;
use Suphle\Request\RequestDetails;
use Suphle\Contracts\Response\RendererManager;
use Suphle\Exception\Explosives\NotFoundException;
use Suphle\Exception\Explosives\DevError\InvalidRendererException;

class AttributeRouteDispatcher implements RouteDispatcher
{
    public function __construct(
        private readonly AttributeRouteManager $routeManager,
        private readonly RequestDetails $requestDetails,
        private readonly RendererManager $rendererManager
    ) {
        //
    }

    public function dispatch(): BaseRenderer
    {
        $path = $this->requestDetails->getPath();
        $method = $this->requestDetails->getMethod();

        // Find matching route
        $route = $this->routeManager->findRoute($path, $method);

        if (!$route) {
            throw new NotFoundException(
                "No route found for {$method} {$path}"
            );
        }

        // Dispatch the route and get response
        $response = $this->dispatchRoute($route);

        // Handle the response
        return $this->handleResponse($response);
    }

    public function dispatchRoute(RouteInfo $route): mixed
    {
        return $this->routeManager->dispatchRoute($route);
    }

    private function handleResponse(mixed $response): BaseRenderer
    {
        if ($response instanceof BaseRenderer) {
            return $response;
        }

        throw new InvalidRendererException(
            'Controller methods must return a framework renderer (Json, Markup, Redirect, Reload, etc.). ' .
            'Returned: ' . (is_object($response) ? get_class($response) : gettype($response))
        );
    }

    public function getRouteInfo(string $path, string $method): ?RouteInfo
    {
        return $this->routeManager->findRoute($path, $method);
    }

    /**
     * @return RouteInfo[]
     */
    public function getAllRoutes(): array
    {
        return $this->routeManager->getAllRoutes();
    }
} 