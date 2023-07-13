<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Crud;

use Suphle\Routing\{BaseCollection, PreMiddlewareRegistry, Decorators\HandlingCoordinator};

use Suphle\Auth\RequestScrutinizers\AuthenticateMetaFunnel;

use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\CrudCoordinator;

#[HandlingCoordinator(CrudCoordinator::class)]
class AuthenticateCrudCollection extends BaseCollection
{
    public function SECURE__SOMEh()
    {

        $this->_crud("secure-some")->registerCruds();
    }

    public function _preMiddleware(PreMiddlewareRegistry $registry): void
    {

        $registry->tagPatterns(
            new AuthenticateMetaFunnel(["EDIT_id"], $this->hydrateAuthStorage())
        );
    }
}
