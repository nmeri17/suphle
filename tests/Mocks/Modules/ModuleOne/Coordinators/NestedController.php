<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\ServiceCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod};
use Suphle\Response\Format\Json;

class NestedController extends ServiceCoordinator
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
