<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\PayloadReaders;

use Suphle\Services\Structures\ModellessPayload;

class CatalogPayload extends ModellessPayload {
    public function convertToDomainObject(): array
    {
        return $this->payloadStorage->getKey('data') ?? [];
    }
} 