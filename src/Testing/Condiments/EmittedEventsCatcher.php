<?php

namespace Suphle\Testing\Condiments;

use Suphle\Events\{EventSubscription, ModuleLevelEvents};

trait EmittedEventsCatcher
{
    abstract protected function getModules(): array;

    protected function assertHandledEvent(
        string $emitter,
        string $eventName = null
    ): void {

        $subscription = $this->getEventSubscription($emitter);

        $this->assertNotNull(
            $subscription,
            "Failed to assert that '$emitter' fired any event"
        );

        if (is_null($eventName)) {
            return;
        }

        $this->assertNotEmpty(
            $subscription->getMatchingUnits($eventName),
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
            $subscription->getMatchingUnits($eventName),
            "Did not expect '$emitter' to fire event '$eventName'"
        );
    }

    private function getEventSubscription(string $sender): ?EventSubscription
    {

        $allSent = $this->getContainer()

        ->getClass(ModuleLevelEvents::class)->getFiredEvents();

        if (array_key_exists($sender, $allSent)) {

            $subscription = $allSent[$sender];

            if (is_null($subscription)) { // was event fired without any paired handlers?
                $subscription = new EventSubscription("", $this->getContainer());
            } // so the asserters don't mistake false positive

            return $subscription;
        }

        return null;
    }
}
