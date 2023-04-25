<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\ServiceCoordinator;

use Suphle\Request\PayloadStorage;

class ReadsPayloadCoordinator extends ServiceCoordinator
{
    public function __construct(protected readonly PayloadStorage $payloadStorage)
    {

        //
    }

    public function mirrorPayload()
    {

        return $this->payloadStorage->fullPayload();
    }
}
