<?php

namespace Suphle\Tests\Integration\Events\BaseTypes;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Events\LocalReceiver};

class TestLocalReceiver extends EventTestCreator
{
    protected string $eventReceiverName = LocalReceiver::class;

    protected function setModuleOne(): void
    {

        $this->moduleOne = $this->bindMockedEventReceiver(ModuleOneDescriptor::class);
    }
}
