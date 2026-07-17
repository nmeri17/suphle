<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\Versions\V2;
use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};

use Suphle\Response\Format\Json;
use Suphle\Services\BaseCoordinator;

// this cascade thing is probably useless atp
#[RoutePrefix("/versions")]
class ApiUpdate2Coordinator extends BaseCoordinator
{
    #[Route("/second")]
    public function secondCascade():Json {

        return new Json([]);
    }
    #[Route("/second-segment")]
    public function segmentInSecond():Json {

        return new Json([]);
    }
}
