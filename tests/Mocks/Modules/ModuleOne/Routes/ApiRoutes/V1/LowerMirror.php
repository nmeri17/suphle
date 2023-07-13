<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\ApiRoutes\V1;

use Suphle\Routing\{BaseApiCollection, PreMiddlewareRegistry, Decorators\HandlingCoordinator};

use Suphle\Auth\RequestScrutinizers\AuthenticateMetaFunnel;

use Suphle\Response\Format\Json;

use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\ApiEntryCoordinator;

#[HandlingCoordinator(ApiEntryCoordinator::class)]
class LowerMirror extends BaseApiCollection
{
    public function API__SEGMENTh()
    {

        $this->_httpGet(new Json("segmentHandler"));
    }

    public function SEGMENT_id()
    {

        $this->_httpGet(new Json("simplePairOverride"));
    }

    public function CASCADE()
    {

        $this->_httpGet(new Json("originalCascade"));
    }

    public function SECURE__SEGMENTh()
    {

        $this->_httpGet(new Json("segmentHandler"));
    }

    public function _preMiddleware(PreMiddlewareRegistry $registry): void
    {

        $registry->tagPatterns(
            new AuthenticateMetaFunnel(
            	["SECURE__SEGMENTh", "CASCADE"],

            	$this->hydrateAuthStorage()
            )
        );
    }
}
