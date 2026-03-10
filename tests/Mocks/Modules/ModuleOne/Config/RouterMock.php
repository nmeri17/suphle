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
            // Useful for test isolation
            \Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\BaseCoordinator::class,
            \Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\ApiEntryCoordinator::class,
            \Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\ApiUpdate2Coordinator::class,
            \Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\ApiUpdate3Coordinator::class,
            \Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\ValidatorCoordinator::class,
            \Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\FlowCoordinator::class,
            \Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\EmploymentEditCoordinator::class,
            \Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\ImageUploadCoordinator::class,
            \Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\SecureCoordinator::class,
            \Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\MiddlewareCoordinator::class,
            \Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\HotwireCoordinator::class,
            \Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\TestCoordinator::class,
            \Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\UserCoordinator::class,
        ];
    }

    public function mirrorsCollections(): bool
    {
        return true;
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
