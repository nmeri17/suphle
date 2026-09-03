<?php
namespace Suphle\Testing\Condiments;

use Suphle\Events\ModuleLevelEvents;

trait EmittedEventsCatcher
{
    abstract protected function getModules(): array;

    protected function assertHandledEvent(string $emitter, string $eventName): void {

        $subscription = $this->getEventSubscription($emitter);

        $this->assertNotNull(
            $subscription,
            "Failed to assert that '$emitter' fired any event"
        );

        $this->assertNotNull(
            @$subscription[$eventName],
            "Failed to assert that '$emitter' emitted an event named '$eventName'"
        );
    }

    protected function assertNotHandledEvent(string $emitter, string $eventName): void
    {

        $subscription = $this->getEventSubscription($emitter);

        if (is_null($subscription)) {

            $this->assertNull($subscription); // to avoid risky test

            return;
        }

        $this->assertEmpty(
            @$subscription[$eventName],
            "Did not expect '$emitter' to fire event '$eventName'"
        );
    }

    private function getEventSubscription(string $sender):?array {

        return @$this->getContainer()

        ->getClass(ModuleLevelEvents::class)->getFiredEvents()[$sender];
    }
}
