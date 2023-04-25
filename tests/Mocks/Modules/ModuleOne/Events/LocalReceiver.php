<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Events;

use Suphle\Events\EmitProxy;

use Suphle\Contracts\Events;

use Suphle\Services\Decorators\VariableDependencies;

use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleApi;

#[VariableDependencies(["setEventManager"])]
class LocalReceiver
{
    use EmitProxy;

    protected ?Events $eventManager = null;

    public const CASCADE_REBOUND_EVENT = "rebounding";

    /**
     * Not necessary on a good day but since this receiver is mocked in tests before the container is available to bind or know module's eventManager, we lazily inject it
    */
    public function setEventManager(Events $eventManager)
    {

        $this->eventManager = $eventManager;
    }

    public function updatePayload($payload): void
    {

        $this->payload = $payload;
    }

    public function doNothing(): void
    {

        //
    }

    public function reboundsNewEvent($payload): void
    {

        $this->emitHelper(self::CASCADE_REBOUND_EVENT, $payload);
    }

    public function unionHandler($payload = null): void
    {

        $this->payload = $payload;
    }

    public function reboundExternalEvent($payload): void
    {

        $this->emitHelper(ModuleApi::OUTSIDERS_REBOUND_EVENT, $payload);
    }
}
