<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix};
use Suphle\Response\Format\Json;

#[RoutePrefix('api/v1/secure')]
class CoordinatorWithParentPrefix extends BaseCoordinator
{
    #[Route('/')]
    public function index(): Json
    {
        return new Json(['message' => 'Secure area']);
    }
} 