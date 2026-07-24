<?php

namespace Suphle\Tests\Mocks\Modules\ModuleThree\PayloadReaders;

use Suphle\Services\Structures\ModellessPayload;

class ReadsId extends ModellessPayload
{
    protected function convertToDomainObject()
    {

        $this->routeInfo->allNumericToPositive();

        return $this->routeInfo->getSegmentValue("id");
    }
}
