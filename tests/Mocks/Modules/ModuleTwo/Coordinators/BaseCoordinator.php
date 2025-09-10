<?php

namespace Suphle\Tests\Mocks\Modules\ModuleTwo\Coordinators;

use Suphle\Services\ServiceCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod};
use Suphle\Response\Format\Json;

class BaseCoordinator extends ServiceCoordinator
{
    #[Route("module-two/{id}", HttpMethod::GET)]
    public function checkPlaceholder(): Json
    {
        return new Json([]);
    }
}
