<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

use Suphle\Services\Decorators\DomainService;

use Illuminate\Support\Collection;

#[DomainService]
class DummyModels
{
    public function fetchModels(int $amount = 10): Collection
    {

        $models = [];

        $amount += 1; // since loop begins at 1 instead of 0

        for ($i=1; $i < $amount; $i++) {
            $models[] = ["id" => $i];
        }

        return new Collection($models);
    }
}
