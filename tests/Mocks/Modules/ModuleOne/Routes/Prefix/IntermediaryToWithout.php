<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

use Suphle\Routing\BaseCollection;

class IntermediaryToWithout extends BaseCollection
{
    public function MIDDLE()
    {

        $this->_prefixFor(NoInnerPrefix::class);
    }
}
