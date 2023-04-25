<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes;

class FlowService
{
    public function customHandlePrevious($payload): iterable
    {

        return array_map(fn ($model) => $model["id"] * 2, $payload["data"]);
    }
}
