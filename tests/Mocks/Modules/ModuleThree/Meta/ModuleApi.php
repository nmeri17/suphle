<?php

namespace Suphle\Tests\Mocks\Modules\ModuleThree\Meta;

use Suphle\Tests\Mocks\Interactions\{ModuleThree, ModuleOne};

class ModuleApi implements ModuleThree
{
    public function __construct(protected readonly ModuleOne $moduleOne)
    {

        //
    }

    public function getLocalValue(): int
    {

        return 10;
    }

    public function changeExternalValueProxy(int $newCount): void
    {

        $this->moduleOne->setBCounterValue($newCount);
    }
}
