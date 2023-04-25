<?php

namespace Suphle\Adapters\Orms\Eloquent\Models;

use Suphle\Contracts\Database\EntityDetails;

use ReflectionClass;

class ModelDetail implements EntityDetails
{
    public function idFromModel(object $model, string $prefix = ""): string
    {

        $primaryField = $model->getKeyName();

        return $this->idFromString(
            $this->getModelName($model::class),
            $model->$primaryField,
            $prefix
        );
    }

    protected function getModelName(string $modelFqcn): string
    {

        return (new ReflectionClass($modelFqcn))->getShortName();
    }

    public function idFromModelName(string $modelFqcn, string $modelId, string $prefix = ""): string
    {

        return $this->idFromString(
            $this->getModelName($modelFqcn),
            $modelId,
            $prefix
        );
    }

    public function idFromString(string $modelName, string $modelId, string $prefix = ""): string
    {

        return strtolower(implode(
            "_",
            array_filter([ // remove possible empty entries

                $prefix, $modelName, $modelId
            ])
        ));
    }
}
