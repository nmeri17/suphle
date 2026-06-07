<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix};
use Suphle\Response\Format\Json;

#[RoutePrefix('')]
class CoordinatorWithoutPrefix extends BaseCoordinator
{
    #[Route('/test')]
    public function test(): Json
    {
        return new Json(['message' => 'This should be ignored']);
    }
} 