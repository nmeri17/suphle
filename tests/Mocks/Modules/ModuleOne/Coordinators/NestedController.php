<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod, RoutePrefix};
use Suphle\Response\Format\Json;

#[RoutePrefix("/nested")]
class NestedController extends BaseCoordinator
{
    #[Route("without")]
    public function noInner(): Json
    {
        return new Json([]);
    }

    #[Route("inner/with")]
    public function hasInner(): Json
    {
        return new Json([]);
    }

    #[Route("third-segment")]
    public function thirdSegmentHandler(): Json
    {
        return new Json([]);
    }

    #[Route("middle/without")]
    public function middleWithout(): Json
    {
        return new Json([]);
    }

    #[Route("middle/third-segment")]
    public function middleThird(): Json
    {
        return new Json([]);
    }

    #[Route("first/middle/third-segment")]
    public function firstMiddleThird(): Json
    {
        return new Json([]);
    }

    #[Route("first/middle/without")]
    public function firstMiddleWithout(): Json
    {
        return new Json([]);
    }
}
