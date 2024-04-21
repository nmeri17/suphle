<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix\Secured;

use Suphle\Routing\{BaseCollection, PreMiddlewareRegistry, Decorators\HandlingCoordinator};

use Suphle\Auth\RequestScrutinizers\AuthenticateMetaFunnel;

use Suphle\Response\Format\Json;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\Prefix\UnchainParentSecurity, Coordinators\BaseCoordinator};

#[HandlingCoordinator(BaseCoordinator::class)]
class UpperCollection extends BaseCollection
{
    public function _preMiddleware(PreMiddlewareRegistry $registry): void
    {

        $registry->tagPatterns(
            new AuthenticateMetaFunnel(["PREFIX"], $this->hydrateAuthStorage())
        );
    }

    public function PREFIX()
    {

        $this->_prefixFor(UnchainParentSecurity::class);
    }

    public function NO__TAGh () {

    	$this->_httpGet(new Json("plainSegment"));
    }
}
