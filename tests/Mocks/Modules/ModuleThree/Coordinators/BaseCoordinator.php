<?php

namespace Suphle\Tests\Mocks\Modules\ModuleThree\Coordinators;

use Suphle\Services\ServiceCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod};
use Suphle\Response\Format\Json;
use Suphle\Tests\Mocks\Modules\ModuleThree\PayloadReaders\ReadsId;

class BaseCoordinator extends ServiceCoordinator
{
    #[Route("module-three/{id}")]
    public function checkPlaceholder(ReadsId $payloadReader): Json
    {
        return new Json([
            "id" => $payloadReader->getDomainObject()
        ]);
    }
}
