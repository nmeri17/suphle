<?php

namespace Suphle\Coordinators;

use Suphle\Contracts\Coordinators\Coordinator;
use Suphle\Hydration\Container;
use Suphle\Request\RequestDetails;
use Suphle\Routing\PathPlaceholders;

abstract class BaseCoordinator implements Coordinator
{
    public function __construct(
        protected readonly Container $container,
        protected readonly RequestDetails $requestDetails,
        protected readonly PathPlaceholders $pathPlaceholders
    ) {
        //
    }

    /**
     * Get the container instance
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Get request details
     */
    public function getRequestDetails(): RequestDetails
    {
        return $this->requestDetails;
    }

    /**
     * Get path placeholders
     */
    public function getPathPlaceholders(): PathPlaceholders
    {
        return $this->pathPlaceholders;
    }
} 