<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes;

use Suphle\Events\EmitProxy;

use Suphle\Contracts\Events;

use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleApi;

class LocalSender
{
    use EmitProxy;

    public const CASCADE_BEGIN_EVENT = "cascading",

    CONCAT_EVENT = "concating",

    CASCADE_EXTERNAL_BEGIN_EVENT = "begin_external_cascade",

    EMPTY_PAYLOAD_EVENT = "no_payload";

    public function __construct(
        protected readonly Events $eventManager
    ) {

        //
    }

    public function sendLocalEvent($payload): void
    {

        $this->emitHelper(ModuleApi::DEFAULT_EVENT, $payload);
    }

    public function sendLocalEventNoPayload(): void
    {

        $this->emitHelper(ModuleApi::EMPTY_PAYLOAD_EVENT);
    }

    public function cascadingEntry($payload): void
    {

        $this->emitHelper(self::CASCADE_BEGIN_EVENT, $payload);
    }

    public function sendConcatHalf($payload): void
    {

        $this->emitHelper(self::CONCAT_EVENT, $payload);
    }

    public function beginExternalCascade($payload): void
    {

        $this->emitHelper(self::CASCADE_EXTERNAL_BEGIN_EVENT, $payload);
    }
}
