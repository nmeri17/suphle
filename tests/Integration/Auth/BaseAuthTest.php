<?php

namespace Suphle\Tests\Integration\Auth;

use Suphle\Contracts\Config\Router as RouterContract;

use Suphle\Config\Router;

use Suphle\Testing\{Condiments\BaseDatabasePopulator, TestTypes\ModuleLevelTest};

use Suphle\Testing\Proxies\{WriteOnlyContainer, SecureUserAssertions};

use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Coordinators\SecureCoordinator};

class BaseAuthTest extends ModuleLevelTest
{
    use BaseDatabasePopulator, SecureUserAssertions;

    protected string $coordinatorName = SecureCoordinator::class;

    protected function getModules(): array {

        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(
                    RouterContract::class, Router::class,
                    [

                        "getCoordinatorClassesToScan" => [$this->coordinatorName]
                    ]
                );
            })
        ];
    }

    protected function getActiveEntity(): string {

        return EloquentUser::class;
    }
}