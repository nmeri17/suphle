<?php

namespace Suphle\Events;

use Suphle\Contracts\Events;

/**
 * Requires an `eventManager` property to be set on using classes. Not specified here to avoid signature clashes
*/
trait EmitProxy
{
    protected function emitHelper(string $eventName, $payload = null): void
    {

        $this->eventManager->emit(static::class, $eventName, $payload);
    }
}
