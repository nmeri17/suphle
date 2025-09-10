<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\ServiceCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix};
use Suphle\Response\Format\Json;

#[RoutePrefix('api/v1/admin')]
class BaseCoordinatorWithPrefix extends ServiceCoordinator
{
    #[Route('/users')]
    public function index(): Json
    {
        return new Json(['message' => 'Admin users list']);
    }
} 