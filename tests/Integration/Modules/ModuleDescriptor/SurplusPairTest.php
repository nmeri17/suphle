<?php

namespace Suphle\Tests\Integration\Modules\ModuleDescriptor;

use Suphle\Hydration\Container;

use Suphle\Tests\Mocks\Interactions\{ModuleThree, ModuleOne };

use Suphle\Tests\Mocks\Modules\ModuleTwo\Meta\ModuleTwoDescriptor;

class SurplusPairTest extends FailingCollection
{
    protected function setModuleTwo(): void
    {

        $this->moduleTwo = (new ModuleTwoDescriptor(new Container()))

        ->sendExpatriates([

            ModuleThree::class => $this->moduleThree,

            ModuleOne::class => $this->moduleOne
        ]);
    }
}
