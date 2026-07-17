<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix};
use Suphle\Response\Format\Json;
use Suphle\Request\PayloadStorage;

#[RoutePrefix('/payload')]
class ReadsPayloadCoordinator extends BaseCoordinator
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
