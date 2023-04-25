<?php

namespace Suphle\Tests\Mocks\Modules\ModuleThree\Routes;

use Suphle\Routing\{BaseCollection, Decorators\HandlingCoordinator};

use Suphle\Tests\Mocks\Modules\ModuleThree\Coordinators\BaseCoordinator;

use Suphle\Response\Format\Json;

#[HandlingCoordinator(BaseCoordinator::class)]
class BrowserCollection extends BaseCollection
{
    public function _prefixCurrent(): string
    {

        return "MODULE__THREEh";
    }

    public function id()
    {

        $this->_httpGet(new Json("checkPlaceholder"));
    }
}
