<?php

namespace Suphle\Events;

use Suphle\Hydration\Container;

/**
 * Used to ensure all events from each emittable entity are handled by one class per subscriber
*/
class EventSubscription
{
    protected array $handlingUnits = [];

    public function __construct(
        protected readonly string $handlingClass,
        protected readonly Container $container
    ) {

        //
    }

    // since each local event manager points to its own module, we can know that pulling a listener from another module will load the class from its correct scope
    public function getListener(): object
    {

        return $this->container->getClass($this->handlingClass);
    }

    public function getMatchingUnits(string $eventName): array
    {

        return array_filter(
            $this->handlingUnits,
            fn (ExecutionUnit $unit) => $unit->matchesEvent($eventName) // return an array of all hits, instead of the first one only so we can chain multiple handlers to one event
        );
    }

    /**
    * @param {eventNames} space separated list of events to be handled by this method
    */
    public function on(string $eventNames, string $handlingMethod): self
    {

        foreach (explode(" ", $eventNames) as $eventName) {

            $this->handlingUnits[] = new ExecutionUnit($eventName, $handlingMethod);
        }

        return $this;
    }
}
