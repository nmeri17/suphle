<?php

namespace Suphle\Tests\Integration\Authorization;

use Suphle\Auth\Middleware\PathAuthorization;

use Suphle\Contracts\{Auth\UserContract, Config\Router as RouterContract};

use Suphle\Config\Router;

use Suphle\Testing\{Condiments\BaseDatabasePopulator, TestTypes\ModuleLevelTest};

use Suphle\Testing\Proxies\{SecureUserAssertions, WriteOnlyContainer};

use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Coordinators\EmploymentEditCoordinator};

abstract class TestPathAuthorizer extends ModuleLevelTest
{
    use SecureUserAssertions, BaseDatabasePopulator {

        BaseDatabasePopulator::setUp as databaseAllSetup;
    }

    protected string $coordinatorName = EmploymentEditCoordinator::class;

    protected function setUp(): void
    {

        $this->databaseAllSetup();

        $this->setUser();
    }

    protected function getModules(): array
    {

        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(RouterContract::class, Router::class, [

                    "getCoordinatorClassesToScan" => [$this->coordinatorName]
                ]);
            })
        ];
    }

    protected function getActiveEntity(): string
    {

        return EloquentUser::class;
    }

    protected function makeUser(bool $makeAdmin = false): UserContract
    {

        return $this->replicator->modifyInsertion(1, [

            "is_admin" => $makeAdmin
        ])->first();
    }

    abstract protected function setUser(): void;
}
