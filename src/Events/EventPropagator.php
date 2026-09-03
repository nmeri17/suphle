<?php
namespace Suphle\Events;

use Suphle\Contracts\Modules\DescriptorInterface;

class EventPropagator {

    protected string $descriptorName;

    public function __construct(
        protected readonly ModuleLevelEvents $moduleLevelEvent,
        DescriptorInterface $module
    ) {

        $this->descriptorName = $module->exportsImplements();
    }

    /**
     * @param {$emitter} inserting this without a proxy means a random class can trigger handlers listening on another event, which is not an entirely safe bet, but can come in handy when building dev-facing functionality @see OuterflowWrapper->emitEvents
     **/
    public function emit(string $emitterClass, string $eventName, $payload = null): void
    {
        $this->moduleLevelEvent->handleEmission(

            $this->descriptorName, $emitterClass, $eventName, $payload
        );
    }
}