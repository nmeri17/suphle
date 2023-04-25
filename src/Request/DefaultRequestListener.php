<?php

namespace Suphle\Request;

use Suphle\Contracts\{Bridge\LaravelContainer, Requests\RequestEventsListener, Config\Laravel as LaravelConfigContract};

use Suphle\Hydration\Container;

class DefaultRequestListener implements RequestEventsListener
{
    public function __construct(
        protected readonly Container $container,
        protected readonly RequestDetails $requestDetails,
        protected readonly LaravelConfigContract $laravelConfig
    ) {

        //
    }

    public function handleRefreshEvent(PayloadStorage $payloadStorage): void
    {

        if (!$this->laravelConfig->registersRoutes()) {
            return;
        }

        $laravelContainer = $this->container->getClass(LaravelContainer::class);

        $laravelContainer->instance(
            LaravelContainer::INCOMING_REQUEST_KEY,
            $laravelContainer->provideRequest(
                $this->requestDetails,
                $payloadStorage
            )
        );
    }
}
