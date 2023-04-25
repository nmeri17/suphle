<?php

namespace Suphle\Tests\Integration\Events;

use Suphle\Tests\Integration\Events\BaseTypes\EventTestCreator;

use Suphle\Tests\Mocks\Interactions\{ModuleOne, ModuleTwo, ModuleThree};

use Suphle\Tests\Mocks\Modules\ModuleTwo\{Meta\ModuleTwoDescriptor, Events\ExternalReactor};

class DecoupledExternalTest extends EventTestCreator
{
    protected string $eventReceiverName = ExternalReactor::class;

    protected function setModuleTwo(): void
    {

        $this->moduleTwo = $this->bindMockedEventReceiver(ModuleTwoDescriptor::class)
        ->sendExpatriates([

            ModuleThree::class => $this->moduleThree
        ]);
    }

    public function test_can_listen_to_unimported_external()
    {

        $this->createMockEventReceiver($this->expectUpdatePayload(), []); // then

        $this->parentSetUp(); // given

        $this->getModuleFor(ModuleOne::class)->payloadEvent($this->payload); // when
    }
}
