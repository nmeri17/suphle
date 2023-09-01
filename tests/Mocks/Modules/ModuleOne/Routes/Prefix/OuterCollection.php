<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

use Suphle\Routing\{BaseCollection, Decorators\HandlingCoordinator};

use Suphle\Response\Format\Json;

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

    // doesn't actually ignore it. The child only prefers its own prefix when the collection is used directly
    public function IGNORE__INTERNALh()
    {

        $this->_prefixFor(WithInnerPrefix::class);
    }

    public function _index () {

    	$this->_httpGet(new Json("getEmptyArray"));
    }
}
