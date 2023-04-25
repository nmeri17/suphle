<?php

namespace Suphle\Tests\Integration\Events;

use Suphle\Tests\Integration\Events\BaseTypes\TestLocalReceiver;

class LocalPayloadTest extends TestLocalReceiver
{
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
