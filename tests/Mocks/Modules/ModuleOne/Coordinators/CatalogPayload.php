<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\Structures\ModellessPayload;

class CatalogPayload extends ModellessPayload
{
    public function getDomainObject(): array
    {
        return $this->payloadStorage->getKey('data') ?? [];
    }
} 