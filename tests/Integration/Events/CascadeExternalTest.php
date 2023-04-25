<?php

namespace Suphle\Tests\Integration\Events;

use Suphle\Tests\Integration\Events\BaseTypes\EventTestCreator;

use Suphle\Tests\Mocks\Interactions\ModuleOne;

use Suphle\Tests\Mocks\Modules\ModuleThree\{Meta\ModuleThreeDescriptor, Events\EventsHandler};

class CascadeExternalTest extends EventTestCreator
{
    protected string $eventReceiverName = EventsHandler::class;

    protected function setModuleThree(): void
    {

        $this->moduleThree = $this->bindMockedEventReceiver(ModuleThreeDescriptor::class)
        ->sendExpatriates([

            ModuleOne::class => $this->moduleOne
        ]);
    }

    public function test_local_emit_cascades_to_external()
    {

        $this->createMockEventReceiver([ // then

            "handleExternalRebound" => [1, [false]]
        ]);

        $this->parentSetUp(); // given

        $this->getModuleOne()->multiModuleCascadeEvent(false); // when
    }
}
