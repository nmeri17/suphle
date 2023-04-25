<?php

namespace Suphle\Tests\Mocks\Modules\ModuleThree\Coordinators;

use Suphle\Services\ServiceCoordinator;

use Suphle\Tests\Mocks\Modules\ModuleThree\PayloadReaders\ReadsId;

class BaseCoordinator extends ServiceCoordinator
{
    public function checkPlaceholder(ReadsId $payloadReader): array
    {

        return [

            "id" => $payloadReader->getDomainObject()
        ];
    }
}
