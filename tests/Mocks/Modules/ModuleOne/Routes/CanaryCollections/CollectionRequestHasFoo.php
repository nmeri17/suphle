<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\CanaryCollections;

use Suphle\Routing\{BaseCollection, Decorators\HandlingCoordinator};

use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\CanaryController;

use Suphle\Response\Format\Json;

#[HandlingCoordinator(CanaryController::class)]
class CollectionRequestHasFoo extends BaseCollection
{
    public function SAME__URLh()
    {

        $this->_httpGet(new Json("fooHandler"));
    }
}
