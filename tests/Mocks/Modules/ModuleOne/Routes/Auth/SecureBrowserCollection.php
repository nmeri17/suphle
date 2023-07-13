<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Auth;

use Suphle\Routing\{BaseCollection, PreMiddlewareRegistry, Decorators\HandlingCoordinator};

use Suphle\Auth\RequestScrutinizers\AuthenticateMetaFunnel;

use Suphle\Response\Format\Json;

use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\BaseCoordinator;

#[HandlingCoordinator(BaseCoordinator::class)]
class SecureBrowserCollection extends BaseCollection
{
    public function SEGMENT()
    {

        $this->_httpGet(new Json("plainSegment"));
    }

    public function _preMiddleware(PreMiddlewareRegistry $registry): void
    {

        $registry->tagPatterns(
            new AuthenticateMetaFunnel(["SEGMENT"], $this->hydrateAuthStorage())
        );
    }
}
