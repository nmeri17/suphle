<?php

namespace Suphle\Tests\Mocks\Modules\ModuleTwo\Events;

use Suphle\Events\EventManager;

use Suphle\Tests\Mocks\Modules\ModuleTwo\Events\ExternalReactor;

use Suphle\Tests\Mocks\Interactions\ModuleOne;

class AssignListeners extends EventManager
{
    public function registerListeners(): void
    {

        parent::registerListeners();

        $this->external(ModuleOne::class, ExternalReactor::class)

        ->on(ModuleOne::DEFAULT_EVENT, "updatePayload");
    }
}
