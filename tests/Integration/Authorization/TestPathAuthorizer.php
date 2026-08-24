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

                    "getCoordinatorClassesToScan" => [EmploymentEditCoordinator::class]
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

    // can't move this to setUp since this object is updated after request is updated
    protected function authorizationSuccess(): bool
    {

        return $this->getContainer()->getClass(PathAuthorization::class)
        ->passesActiveRules();
    }

    abstract protected function setUser(): void;
}
