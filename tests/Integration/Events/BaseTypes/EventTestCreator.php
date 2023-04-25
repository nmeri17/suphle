<?php

namespace Suphle\Tests\Integration\Events\BaseTypes;

use Suphle\Contracts\Modules\DescriptorInterface;

use Suphle\Testing\Proxies\WriteOnlyContainer;

use Suphle\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

use Suphle\Tests\Mocks\Interactions\ModuleOne;

class EventTestCreator extends DescriptorCollection
{
    protected $payload = 5;

    protected object $doubledEventReceiver; // they're POPOs

    protected string $eventReceiverName;

    // since we intend to manually trigger it in extending tests
    protected function setUp(): void
    {
    }

    protected function parentSetUp(): void
    {

        parent::setUp();
    }

    protected function getModuleOne(): ModuleOne
    {

        return $this->getModuleFor(ModuleOne::class);
    }

    /**
     * The receiver, [eventReceiverName], will be replaced in the listening module with a mock allowing us know whether it actually handled event
     *
     * @param {descriptorName}: The module receiving the event to be emitted
     *
     * @return new module with updates
    */
    protected function bindMockedEventReceiver(string $descriptorName): DescriptorInterface
    {

        return $this->replicateModule($descriptorName, function (WriteOnlyContainer $container) {

            $container->replaceWithConcrete(
                $this->eventReceiverName,
                $this->doubledEventReceiver
            );
        });
    }

    /**
     * Intended to be called before [setUp]
    */
    protected function createMockEventReceiver(array $mockMethods, array $constructorStubs = null): void
    {

        $this->doubledEventReceiver = $this->positiveDouble( // can't use [replaceConstructorArguments] since that requires container and that isn't available here

            $this->eventReceiverName,
            [],
            $mockMethods,
            $constructorStubs
        );
    }

    protected function expectUpdatePayload(): array
    {

        return [

            "updatePayload" => [1, [$this->payload]]
        ];
    }
}
