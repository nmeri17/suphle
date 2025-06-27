<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Coordinators\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod};

class CoordinatorWithoutPrefix extends BaseCoordinator
{
    #[Route('/test', method: HttpMethod::GET)]
    public function test(): array
    {
        return ['message' => 'This should be ignored'];
    }
} 