<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Coordinators\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};

#[RoutePrefix('api/v1/admin')]
class BaseCoordinatorWithPrefix extends BaseCoordinator
{
    #[Route('/users', method: HttpMethod::GET)]
    public function index(): array
    {
        return ['message' => 'Admin users list'];
    }
} 