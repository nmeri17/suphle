<?php

namespace Suphle\Tests\Integration\Events;

use Suphle\Tests\Integration\Events\BaseTypes\TestLocalReceiver;

class GroupedEventsTest extends TestLocalReceiver
{
    public function test_space_delimited_event_names()
    {

        $this->createMockEventReceiver([ // then

            "doNothing" => [1, []],

            "unionHandler" => [2, []]
        ]); // then

        $this->parentSetUp(); // given

        $this->getModuleOne()->sendConcatEvents($this->payload); // when
    }
}
