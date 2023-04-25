<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Middlewares;

use Suphle\Routing\{BaseCollection, Decorators\HandlingCoordinator};

use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\BaseCoordinator;

use Suphle\Response\Format\Json;

#[HandlingCoordinator(BaseCoordinator::class)]
class RetainsMiddleware extends BaseCollection
{
    public function SEGMENT()
    {

        $this->_httpGet(new Json("plainSegment"));
    }
}
