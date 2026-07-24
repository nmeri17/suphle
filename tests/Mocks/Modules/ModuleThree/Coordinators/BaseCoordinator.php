<?php

namespace Suphle\Tests\Mocks\Modules\ModuleThree\Coordinators;

use Suphle\Services\BaseCoordinator as SuphleCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod, RoutePrefix};
use Suphle\Response\Format\Json;
use Suphle\Tests\Mocks\Modules\ModuleThree\PayloadReaders\ReadsId;

#[RoutePrefix("/module-three")]
class BaseCoordinator extends SuphleCoordinator {
    
    #[Route("/{id}")]
    public function checkPlaceholder(ReadsId $payloadReader): Json
    {
        return new Json([
            "id" => $payloadReader->getDomainObject()
        ]);
    }
}
