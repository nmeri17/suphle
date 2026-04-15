<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Config;

use Suphle\Config\Router;

use Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares\Collectors\{BlankCollectionMetaFunnel, BlankMiddleware2Collector, BlankMiddleware3Collector};

use Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares\{BlankMiddlewareHandler, BlankMiddleware2Handler, BlankMiddleware3Handler};

class RouterMock extends Router
{
    public function getCoordinatorClassesToScan(): array
    {
        return [
            // List specific coordinator classes to scan, or empty array for all
        ];
    }

    /**
     * {@inheritdoc}
    */
    public function collectorHandlers(): array
    {
        return array_merge(parent::collectorHandlers(), [
            BlankCollectionMetaFunnel::class => BlankMiddlewareHandler::class,
            BlankMiddleware2Collector::class => BlankMiddleware2Handler::class,
            BlankMiddleware3Collector::class => BlankMiddleware3Handler::class
        ]);
    }
}
