<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\Versions\V3;
use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};

use Suphle\Services\BaseCoordinator;

#[RoutePrefix("/versions")]
class ApiUpdate3Coordinator extends BaseCoordinator
{
    public function thirdCascade()
    {

        //
    }
}
