<?php

namespace Suphle\Tests\Mocks\Modules\ModuleTwo\Coordinators;

use Suphle\Services\BaseCoordinator as SuphleCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod, RoutePrefix};
use Suphle\Response\Format\Json;

#[RoutePrefix("/module-two")]
class BaseCoordinator extends SuphleCoordinator {
    #[Route("/{id}", HttpMethod::GET)]
    public function checkPlaceholder(): Json
    {
        return new Json([]);
    }
}
