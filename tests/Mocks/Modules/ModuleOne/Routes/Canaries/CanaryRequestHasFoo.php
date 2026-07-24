<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Canaries;

use Suphle\Contracts\Routing\CanaryEvaluator;

use Suphle\Request\PayloadStorage;

use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\CanaryCollections\CollectionRequestHasFoo;

class CanaryRequestHasFoo implements CanaryEvaluator
{
    public function __construct(protected readonly PayloadStorage $payloadStorage)
    {

        //
    }

    public function willLoad(): bool
    {

        return $this->payloadStorage->hasKey("foo");
    }

    public function entryClass(): string
    {

        return CollectionRequestHasFoo::class;
    }
}
