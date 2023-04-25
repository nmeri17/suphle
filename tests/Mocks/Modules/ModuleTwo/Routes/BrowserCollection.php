<?php

namespace Suphle\Tests\Mocks\Modules\ModuleTwo\Routes;

use Suphle\Routing\{BaseCollection, Decorators\HandlingCoordinator};

use Suphle\Tests\Mocks\Modules\ModuleTwo\Coordinators\BaseCoordinator;

use Suphle\Response\Format\Json;

#[HandlingCoordinator(BaseCoordinator::class)]
class BrowserCollection extends BaseCollection
{
    public function MODULE__TWOh_id()
    {

        $this->_httpGet(new Json("checkPlaceholder"));
    }
}
