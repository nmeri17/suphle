<?php

namespace Suphle\Tests\Integration\Modules\ModuleDescriptor;

use Suphle\Hydration\Container;

use Suphle\Tests\Mocks\Modules\ModuleTwo\Meta\ModuleTwoDescriptor;

class InadequatePairTest extends FailingCollection
{
    protected function setModuleTwo(): void
    {

        $this->moduleTwo = (new ModuleTwoDescriptor(new Container()));
    }
}
