<?php

namespace Suphle\Contracts;

interface Events
{
    public function registerListeners(): void;

    /**
     * @param {$emitter} inserting this without a proxy means a random class can trigger handlers listening on another event, which is not an entirely safe bet, but can come in handy when building dev-facing functionality @see OuterflowWrapper->emitEvents
     **/
    public function emit(string $emitter, string $eventName, $payload = null): void;
}
