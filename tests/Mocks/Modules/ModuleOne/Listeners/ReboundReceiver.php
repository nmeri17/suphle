<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Listeners;

use Suphle\Events\Attributes\{HasHandlers, EventListener};

#[HasHandlers(LocalReceiver::class)]
class ReboundReceiver
{
    #[EventListener(LocalReceiver::CASCADE_REBOUND_EVENT)]
    public function ricochetReactor($payload): void
    {

        //
    }
}
