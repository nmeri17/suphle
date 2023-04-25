<?php

namespace Suphle\Tests\Integration\Events;

use Suphle\Tests\Integration\Events\BaseTypes\EventTestCreator;

use Suphle\Tests\Mocks\Interactions\ModuleOne;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Events\ReboundReceiver, Meta\ModuleOneDescriptor};

class CascadeTest extends EventTestCreator
{
    protected string $eventReceiverName = ReboundReceiver::class;

    protected function setModuleOne(): void
    {

        $this->moduleOne = $this->bindMockedEventReceiver(ModuleOneDescriptor::class);
    }

    public function test_local_emit_cascades_to_local()
    {

        $this->createMockEventReceiver([

            "ricochetReactor" => [1, [$this->payload]]
        ]); // then

        $this->parentSetUp(); // given

        $this->getModuleOne()->cascadeEntryEvent($this->payload); // when
    }
}
