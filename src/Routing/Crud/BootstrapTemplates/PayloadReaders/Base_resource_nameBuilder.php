<?php

namespace _modules_shell\_module_name\PayloadReaders;

use Suphle\Services\Structures\ModelfulPayload;

use _database_namespace\_resource_name;

/**
 * Only useful with endpoints that fetch from the builder
*/
class Base_resource_nameBuilder extends ModelfulPayload
{
    public function __construct(protected readonly _resource_name $blankModel)
    {

        //
    }

    protected function getBaseCriteria(): object
    {

        return $this->blankModel->where($this->payloadStorage->only(["id"]));
    }

    protected function onlyFields(): array
    {

        return ["id", "name"];
    }
}
