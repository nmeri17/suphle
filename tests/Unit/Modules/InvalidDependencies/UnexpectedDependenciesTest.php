<?php

namespace Suphle\Tests\Unit\Modules\InvalidDependencies;

use Suphle\Hydration\Container;

use Suphle\Exception\Explosives\DevError\UnexpectedModules;

use Suphle\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

use Suphle\Tests\Mocks\Interactions\ModuleOne;

use Suphle\Tests\Mocks\Modules\ModuleTwo\Meta\ModuleTwoDescriptor;

class UnexpectedDependenciesTest extends DescriptorCollection
{
    protected function setUp(): void
    {
    }

    protected function setModuleTwo(): void
    {

        $this->moduleTwo = (new ModuleTwoDescriptor(new Container()))

        ->sendExpatriates([

            ModuleOne::class => $this->moduleOne
        ]);
    }

    public function test_will_complain_when_missing_dependencies()
    {

        $this->expectException(UnexpectedModules::class); // then

        // given @see module creation

        parent::setUp(); // when
    }
}
