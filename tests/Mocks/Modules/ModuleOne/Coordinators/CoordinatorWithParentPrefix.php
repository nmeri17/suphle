<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Coordinators\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};

#[RoutePrefix('api/v1/secure')]
class CoordinatorWithParentPrefix extends BaseCoordinator
{
    #[Route('/', method: HttpMethod::GET)]
    public function index(): array
    {
        return ['message' => 'Secure area'];
    }
} 