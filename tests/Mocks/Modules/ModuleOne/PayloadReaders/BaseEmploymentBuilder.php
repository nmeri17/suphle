<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\PayloadReaders;

use Suphle\Services\Structures\ModelfulPayload;

use Suphle\Tests\Mocks\Models\Eloquent\Employment;

class BaseEmploymentBuilder extends ModelfulPayload
{
    public function __construct(protected readonly Employment $blankEmployment)
    {

        //
    }

    protected function getBaseCriteria(): object
    {

        return $this->blankEmployment->where([

            "id" => $this->payloadStorage->getKey("id")
        ]);
    }

    protected function onlyFields(): array
    {

        return ["id", "title"];
    }
}
