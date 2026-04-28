<?php
namespace _modules_shell\_module_name\PayloadReaders;

use Suphle\Services\Structures\ModelfulPayload;
use Suphle\Contracts\Services\Models\IntegrityModel;
use _database_namespace\_resource_name;

class Base_resource_nameBuilder extends ModelfulPayload
{
    public function __construct(protected readonly _resource_name $blankModel) {}

    protected function getBaseCriteria(): object
    {
        return $this->blankModel->where([
            "id" => $this->routeInfo->getSegmentValue("id") ??

            $this->payloadStorage->getKey("id")
        ]);
    }

    protected function onlyFields(): array
    {
        return ["id", "name", "description", IntegrityModel::INTEGRITY_COLUMN]; // updated_at is needed for collision check
    }
}