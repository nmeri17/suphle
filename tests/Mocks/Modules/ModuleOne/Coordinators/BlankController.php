<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\ServiceCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod};
use Suphle\Response\Format\Json;

class BlankController extends ServiceCoordinator
{
    #[Route("outer")]
    public function getEmptyArray(): Json
    {
        return new Json([]);
    }
}
