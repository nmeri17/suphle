<?php

namespace Suphle\Tests\Mocks\Modules\ModuleTwo\Coordinators;

use Suphle\Services\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod, RoutePrefix};
use Suphle\Response\Format\Json;

#[RoutePrefix("/module-two")]
class BaseCoordinator extends BaseCoordinator
{
    #[Route("/{id}", HttpMethod::GET)]
    public function checkPlaceholder(): Json
    {
        return new Json([]);
    }
}
