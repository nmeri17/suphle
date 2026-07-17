<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix};
use Suphle\Response\Format\Json;

#[RoutePrefix('api/v1')]
class ApiEntryCoordinator extends BaseCoordinator
{
    #[Route('api-segment')]
    public function segmentHandler(): Json
    {
        return new Json([]);
    }

    #[Route('segment/{id}')]
    public function simplePairOverride(): Json
    {
        return new Json([]);
    }

    #[Route('cascade')]
    public function originalCascade(): Json
    {
        return new Json([]);
    }
}
