<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix\Secured;

use Suphle\Routing\{BaseCollection, PreMiddlewareRegistry};

use Suphle\Middleware\MiddlewareRegistry;

use Suphle\Auth\RequestScrutinizers\AuthenticateMetaFunnel;

use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix\IntermediaryToWithout;

use Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares\Collectors\BlankCollectionMetaFunnel;

class MisleadingEntry extends BaseCollection
{
    public function _preMiddleware(PreMiddlewareRegistry $registry): void
    {

        $registry->tagPatterns(
            new AuthenticateMetaFunnel(["FIRST"], $this->authStorage)
        );
    }

    public function _assignMiddleware(MiddlewareRegistry $registry): void
    {

        $registry->tagPatterns(new BlankCollectionMetaFunnel(["FIRST"]));
    }

    public function FIRST()
    {

        $this->_prefixFor(IntermediaryToWithout::class);
    }
}
