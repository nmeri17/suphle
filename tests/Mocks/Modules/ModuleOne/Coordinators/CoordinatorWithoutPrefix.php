<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\ServiceCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix};
use Suphle\Response\Format\Json;

#[RoutePrefix('')]
class CoordinatorWithoutPrefix extends ServiceCoordinator
{
    #[Route('/test')]
    public function test(): Json
    {
        return new Json(['message' => 'This should be ignored']);
    }
} 