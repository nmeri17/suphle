<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Listeners;

use Suphle\Events\{EmitProxy, EventPropagator};

use Suphle\Events\Attributes\{HasHandlers, EventListener};

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleApi, Services\EmitterService};

#[HasHandlers(EmitterService::class)]
class LocalReceiver
{
    use EmitProxy;

    public const CASCADE_REBOUND_EVENT = "rebounding";

    public function __construct(protected readonly EventPropagator $eventEmitter) { }

    #[EventListener(ModuleApi::DEFAULT_EVENT)] // this should not work
    public function updatePayload($payload): void
    {

        $this->payload = $payload;
    }

    #[EventListener(EmitterService::EMPTY_PAYLOAD_EVENT)]
    public function doNothing(): void
    {

        //
    }

    #[EventListener(EmitterService::CASCADE_BEGIN_EVENT)]
    public function reboundsNewEvent($payload): void
    {

        $this->emitHelper(self::CASCADE_REBOUND_EVENT, $payload);
    }

    #[EventListener(EmitterService::EMPTY_PAYLOAD_EVENT)]
    public function unionHandler($payload = null): void
    {

        $this->payload = $payload;
    }

    #[EventListener(EmitterService::EMPTY_PAYLOAD_EVENT)]
    public function unionHandler($payload = null): void
    {

        $this->payload = $payload;
    }

    #[EventListener(EmitterService::CONCAT_EVENT)]
    public function unionConcatHandler($payload = null): void
    {

        $this->payload = $payload;
    }

    #[EventListener(EmitterService::CASCADE_EXTERNAL_BEGIN_EVENT)]
    public function reboundExternalEvent($payload): void
    {

        $this->emitHelper(ModuleApi::OUTSIDERS_REBOUND_EVENT, $payload);
    }
}
