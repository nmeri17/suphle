<?php

namespace Suphle\Config;

use Suphle\Contracts\Config\ComponentTemplates;

use Suphle\Exception\ComponentEntry as ExceptionComponentEntry;

use Suphle\Bridge\Laravel\ComponentEntry as LaravelComponentEntry;

use Suphle\Adapters\Orms\Eloquent\ComponentEntry as EloquentComponentEntry;

use Suphle\Services\ComponentEntry as ServicesComponentEntry;

class DefaultTemplateConfig implements ComponentTemplates
{
    public function getTemplateEntries(): array
    {

        return [
            ExceptionComponentEntry::class,

            LaravelComponentEntry::class,

            EloquentComponentEntry::class,

            ServicesComponentEntry::class
        ];
    }
}
