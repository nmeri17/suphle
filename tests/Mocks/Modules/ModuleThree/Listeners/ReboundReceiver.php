<?php

namespace Suphle\Tests\Mocks\Modules\ModuleThree\Listeners;

use Suphle\Events\Attributes\{HasHandlers, EventListener};

#[HasHandlers(EventsHandler::class)]
class ReboundReceiver
{
    #[EventListener(EventsHandler::EXTERNAL_LOCAL_REBOUND)]
    public function handleMultiModuleRebound($payload): void
    {

        //
    }
}
