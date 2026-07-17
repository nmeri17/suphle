<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\Versions\V1;

use Suphle\Services\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};
use Suphle\Response\Format\Json;

#[RoutePrefix('api/v1')]
class V1Coordinator extends BaseCoordinator
{
    #[Route('/cascade')]
    public function cascade(): Json
    {
        return new Json(['version' => 'v1', 'message' => 'cascade']);
    }

    #[Route('/segment')]
    public function segment(): Json
    {
        return new Json(['version' => 'v1', 'message' => 'segment']);
    }
} 