<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

use Suphle\Routing\{BaseCollection, Decorators\HandlingCoordinator};

use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\BlankController;

#[HandlingCoordinator(BlankController::class)]
class OuterCollection extends BaseCollection
{
    public function _prefixCurrent(): string
    {

        return "OUTER";
    }

    public function USE__METHODh()
    {

        $this->_prefixFor(NoInnerPrefix::class);
    }

    public function IGNORE__INTERNALh()
    {

        $this->_prefixFor(WithInnerPrefix::class);
    }
}
