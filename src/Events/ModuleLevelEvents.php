<?php

namespace Suphle\Events;

use Suphle\Contracts\{Events, Modules\DescriptorInterface};

use Suphle\Hydration\Container;

use Suphle\Modules\Structures\ActiveDescriptors;

class ModuleLevelEvents
{
    protected array $subscriberLog = [];
    protected array // this is where subscribers to the immediate last fired external event reside

    $eventManagers = [];
    protected array $firedEvents = [];

    public function bootReactiveLogger(ActiveDescriptors $descriptorsHolder): void
    {

        foreach (
            $descriptorsHolder->getOriginalDescriptors()

            as $descriptor
        ) {

            $descriptorApi = $descriptor->exportsImplements();

            if (array_key_exists($descriptorApi, $this->eventManagers)) { // the essence of keying by module name is to prevent multiple manager watching while recursively binding modules and their expatriates

                continue;
            }

            $container = $descriptor->getContainer();

            $container->whenTypeAny()->needsAny([ // bind before hydrating

                ModuleLevelEvents::class => $this
            ]);

            $this->eventManagers[$descriptorApi] = $manager = $container->getClass(Events::class);

            $manager->registerListeners();
        }
    }

    public function triggerExternalHandlers(string $emittor, string $eventName, $payload): void
    {

        $subscriberLog = [];

        foreach ($this->eventManagers as $manager) {

            if ($subscribers = $manager->getExternalHandlers($emittor)) {

                $subscriberLog[] = $subscribers;
            }
        }

        foreach ($subscriberLog as $subscription) {

            $this->triggerHandlers(
                $emittor,
                $subscription,
                $eventName,
                $payload
            );
        }
    }

    public function triggerHandlers(
        string $sender,
        ?EventSubscription $subscription,
        string $eventName,
        $payload
    ): self {

        $this->firedEvents[$sender] = $subscription; // even though event won't be handled, by logging it all the same, we can verify later that it was emitted

        if (is_null($subscription)) {
            return $this;
        } // no handlers attached to given scope

        $hydratedHandler = $subscription->getListener();

        foreach ($subscription->getMatchingUnits($eventName) as $unit) {

            $unit->fire($hydratedHandler, $payload);
        }

        return $this;
    }

    /**
     * Used only in tests and should be a test-only class but that would hamper DX such that that observer class must be bound during all module builds, since event binding is part of module booting sequence. May be worth it if there were more methods
    */
    public function getFiredEvents(): array
    {

        return $this->firedEvents;
    }
}
