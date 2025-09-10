<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\ServiceCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix};
use Suphle\Response\Format\Json;
use Suphle\Request\PayloadStorage;

#[RoutePrefix('')]
class ReadsPayloadCoordinator extends ServiceCoordinator
{
    public function __construct(protected readonly PayloadStorage $payloadStorage)
    {
        //
    }

    #[Route("all-payload")]
    public function mirrorPayload(): Json
    {
        return new Json($this->payloadStorage->fullPayload());
    }
}
