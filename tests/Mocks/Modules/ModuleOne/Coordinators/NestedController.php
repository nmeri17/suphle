<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\ServiceCoordinator;

class NestedController extends ServiceCoordinator
{
    public function noInner()
    {

        return [];
    }

    public function hasInner()
    {

        return [];
    }

    public function thirdSegmentHandler()
    {

        return [];
    }
}
