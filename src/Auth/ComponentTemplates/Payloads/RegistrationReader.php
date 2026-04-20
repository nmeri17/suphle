<?php
namespace _modules_shell\_module_name\SuphleIdentity\Payloads;

use Suphle\Services\Structures\ModellessPayload;

class RegistrationReader extends ModellessPayload {
    
    protected function convertToDomainObject (): array {
        return [
            "name" => $this->payloadStorage->getKey("name"),
            "email" => $this->payloadStorage->getKey("email"),
            "password" => $this->payloadStorage->getKey("password")
        ];
    }
}