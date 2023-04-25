<?php

namespace Suphle\Contracts\Database;

interface EntityDetails
{
    public function idFromModel(object $model, string $prefix = ""): string;

    public function idFromString(string $modelName, string $modelId, string $prefix = ""): string;
}
