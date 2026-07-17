<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod, RoutePrefix};
use Suphle\Response\Format\Json;

#[RoutePrefix("/blank")]
class BlankController extends BaseCoordinator
{
    #[Route("/outer")]
    public function getEmptyArray(): Json
    {
        return new Json([]);
    }
}
