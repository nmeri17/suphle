<?php

namespace Suphle\Tests\Integration\Authorization;

use Suphle\Auth\RequestScrutinizers\PathAuthorizationScrutinizer;

use Suphle\Contracts\{Auth\UserContract, Config\Router};

use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

use Suphle\Testing\{Condiments\BaseDatabasePopulator, TestTypes\ModuleLevelTest};

use Suphle\Testing\Proxies\{SecureUserAssertions, WriteOnlyContainer};

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Routes\Auth\AuthorizeRoutes, Config\RouterMock};

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

                $container->replaceWithMock(Router::class, RouterMock::class, [

                    "browserEntryRoute" => AuthorizeRoutes::class
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
    protected function getAuthorizer(): PathAuthorizationScrutinizer
    {

        return $this->getContainer()->getClass(PathAuthorizationScrutinizer::class);
    }

    protected function authorizationSuccess(): bool
    {

        return $this->getAuthorizer()->passesActiveRules();
    }

    abstract protected function setUser(): void;
}
