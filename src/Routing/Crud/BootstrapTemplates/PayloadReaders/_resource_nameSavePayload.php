<?php
namespace _modules_shell\_module_name\PayloadReaders;

use Suphle\Services\Structures\ModellessPayload;

class _resource_nameSavePayload extends ModellessPayload
{
    /**
     * This is the only place in the app that knows the 
     * specific keys coming from the frontend/API client.
     */
    protected function convertToDomainObject(): array
    {
        return [
            "name" => $this->payloadStorage->getKey("name"),
            "description" => $this->payloadStorage->getKey("description")
        ];
    }
}