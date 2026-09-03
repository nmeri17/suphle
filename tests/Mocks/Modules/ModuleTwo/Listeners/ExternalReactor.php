<?php

namespace Suphle\Tests\Mocks\Modules\ModuleTwo\Listeners;

use Suphle\Events\Attributes\{HasHandlers, EventListener};

use Suphle\Tests\Mocks\Interactions\ModuleOne;

#[HasHandlers(ModuleOne::class, isOuterModule: true)]
class ExternalReactor
{
    private $payload;

    #[EventListener(ModuleOne::DEFAULT_EVENT)]
    public function updatePayload($payload): void
    {

        $this->payload = $payload;
    }
}
