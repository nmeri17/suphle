<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Coordinators\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};
use Suphle\Response\Format\Json;

#[RoutePrefix('api/v1/users')]
class PrefixedCoordinator extends BaseCoordinator
{
    #[Route('/', method: HttpMethod::GET)]
    public function index(): Json
    {
        return new Json(['users' => ['Jane', 'John']]);
    }

    #[Route('/{id}', method: HttpMethod::GET)]
    public function show(): Json
    {
        $id = $this->pathPlaceholders->getSegmentValue('id');
        return new Json(['user' => ['id' => $id, 'name' => 'John Doe']]);
    }
} 