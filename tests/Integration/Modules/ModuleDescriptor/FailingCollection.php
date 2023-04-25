<?php

namespace Suphle\Tests\Integration\Modules\ModuleDescriptor;

use Suphle\Exception\Explosives\DevError\UnexpectedModules;

use Suphle\Tests\Mocks\Interactions\ModuleTwo;

abstract class FailingCollection extends DescriptorCollection
{
    protected function setUp(): void
    {

        //
    }

    public function test_will_throw_errors()
    {

        $this->expectException(UnexpectedModules::class); // then

        parent::setUp();

        $this->getModuleFor(ModuleTwo::class); // when
    }
}
