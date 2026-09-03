<?php
namespace Suphle\Events;

/**
 * Requires an `eventEmitter` property to be set on using classes. Not specified here to avoid signature clashes
*/
trait EmitProxy
{
    protected function emitHelper(string $eventName, $payload = null): void
    {

        $this->eventEmitter->emit(static::class, $eventName, $payload);
    }
}
