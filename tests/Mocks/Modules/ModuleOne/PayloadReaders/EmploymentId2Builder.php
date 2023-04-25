<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\PayloadReaders;

class EmploymentId2Builder extends BaseEmploymentBuilder
{
    protected function getBaseCriteria(): object
    {

        return $this->blankEmployment->where([

            "id" => $this->payloadStorage->getKey("id2")
        ]);
    }
}
