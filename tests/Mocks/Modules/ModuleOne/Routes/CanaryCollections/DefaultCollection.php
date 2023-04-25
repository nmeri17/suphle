<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\CanaryCollections;

use Suphle\Routing\{BaseCollection, Decorators\HandlingCoordinator};

use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\CanaryController;

use Suphle\Response\Format\Json;

#[HandlingCoordinator(CanaryController::class)] // in a real app, these canaries and their collections will point to different controllers
class DefaultCollection extends BaseCollection
{
    public function SAME__URLh()
    {

        $this->_httpGet(new Json("defaultHandler"));
    }

    public function id()
    {

        $this->_httpGet(new Json("defaultPlaceholder"));
    }
}
