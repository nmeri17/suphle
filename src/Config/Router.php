<?php

namespace Suphle\Config;

use Suphle\Contracts\Config\Router as RouterConfig;

use Suphle\Middleware\Handlers\{FinalHandlerWrapper, CsrfMiddleware};

class Router implements RouterConfig
{

    /**
     * {@inheritdoc}
    */
    public function defaultMiddleware(): array
    {
        return [
            CsrfMiddleware::class,
            FinalHandlerWrapper::class
        ];
    }

    /**
     * {@inheritdoc}
    */
    public function getCoordinatorPath(): string
    {
        return "Coordinators";
    }

    /**
     * {@inheritdoc}
    */
    public function getWebSocketPath(): string {

        return "Websockets";
    }

    /**
     * {@inheritdoc}
    */
    public function getCoordinatorClassesToScan(): array
    {
        return []; // Scan all coordinators by default
    }

    public function matchesApi (string $path):bool {

        return str_contains(strtolower($path), "/api/");
    }
}
