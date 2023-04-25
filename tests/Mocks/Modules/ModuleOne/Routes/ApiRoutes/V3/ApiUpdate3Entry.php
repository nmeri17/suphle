<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\ApiRoutes\V3;

use Suphle\Routing\{BaseApiCollection, Decorators\HandlingCoordinator};

use Suphle\Response\Format\Json;

use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\Versions\V3\ApiUpdate3Coordinator;

#[HandlingCoordinator(ApiUpdate3Coordinator::class)]
class ApiUpdate3Entry extends BaseApiCollection
{
    public function CASCADE()
    {

        $this->_httpGet(new Json("thirdCascade"));
    }
}
