<?php

namespace Suphle\Tests\Mocks\Modules\ModuleThree\Listeners;

use Suphle\Events\{EmitProxy, EventPropagator};

use Suphle\Events\Attributes\{HasHandlers, EventListener};

use Suphle\Contracts\Config\Events;

use Suphle\Tests\Mocks\Interactions\ModuleOne;

#[HasHandlers(ModuleOne::class, isOuterModule: true)]
class EventsHandler
{
    use EmitProxy;

    public const EXTERNAL_LOCAL_REBOUND = "local_external_local";

    private $payload;

    public function __construct(protected readonly EventPropagator $eventEmitter)
    { }

    #[EventListener(ModuleOne::DEFAULT_EVENT)]
    public function setExternalPayload(int $payload)
    {

        $this->payload = $payload;
    }

    #[EventListener(ModuleOne::OUTSIDERS_REBOUND_EVENT)]
    public function handleExternalRebound(bool $reboundInExternal)
    {

        if ($reboundInExternal) {

            $this->emitHelper(self::EXTERNAL_LOCAL_REBOUND);
        }
    }
}
