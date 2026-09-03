<?php
namespace Suphle\Tests\Mocks\Modules\ModuleOne\Listeners;

use Suphle\Events\Attributes\{HasHandlers, EventListener};

use Suphle\Tests\Mocks\Modules\ModuleOne\Services\UpdatefulEmitter;

use Exception;

#[HasHandlers(UpdatefulEmitter::class)]
class UpdatefulListener
{
    #[EventListener(UpdatefulEmitter::UPDATE_ERROR)]
    public function terminateTransaction($payload): void
    {

        throw new Exception();
    }
}
