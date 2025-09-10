<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Coordinators\ServiceCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix};
use Suphle\Response\Format\Json;

#[RoutePrefix('api/v1/users')]
class PrefixedCoordinator extends ServiceCoordinator
{
    #[Route('/')]
    public function index(): Json
    {
        return new Json(['users' => ['Jane', 'John']]);
    }

    #[Route('/{id}')]
    public function show(): Json
    {
        $id = $this->pathPlaceholders->getSegmentValue('id');
        return new Json(['user' => ['id' => $id, 'name' => 'John Doe']]);
    }
} 