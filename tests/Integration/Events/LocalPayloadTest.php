<?php

namespace Suphle\Tests\Integration\Events;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Listeners\LocalReceiver};

use Suphle\Tests\Integration\Events\BaseTypes\EventTestCreator;

class LocalPayloadTest extends EventTestCreator
{
    protected string $eventReceiverName = LocalReceiver::class;

    protected function setModuleOne(): void
    {

        $this->moduleOne = $this->bindMockedEventReceiver(ModuleOneDescriptor::class);
    }
    public function test_can_receive_emitted_payload()
    {

        $this->createMockEventReceiver($this->expectUpdatePayload()); // then

        $this->parentSetUp(); // given

        $this->getModuleOne()->payloadEvent($this->payload); // when
    }

    // we listen on the parent, then a child emits
    public function test_listeners_can_listen_to_subclass_emittor()
    {

        $this->createMockEventReceiver($this->expectUpdatePayload()); // then

        $this->parentSetUp(); // given

        $this->getModuleOne()->sendExtendedEvent($this->payload); // when
    }
}
