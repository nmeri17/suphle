<?php

namespace Suphle\Config;

use Suphle\Contracts\Config\Flows;

use Illuminate\Support\Collection as LaravelCollection;

class DefaultFlowConfig implements Flows
{
    /**
     * {@inheritdoc}
    */
    public function contentTypeIdentifier(): array
    {

        return [

            LaravelCollection::class => "getQueueableClass"
        ];
    }

    /**
     * {@inheritdoc}
    */
    public function isEnabled(): bool
    {

        return false;
    }
}
