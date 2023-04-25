<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\ServiceCoordinator;

class CanaryController extends ServiceCoordinator
{
    public function user5Handler()
    {

        //
    }

    public function fooHandler(): array
    {

        return [];
    }

    public function defaultHandler()
    {

        //
    }

    public function defaultPlaceholder()
    {

        //
    }
}
