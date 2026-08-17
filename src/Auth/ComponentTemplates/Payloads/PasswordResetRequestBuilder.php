<?php

namespace _modules_shell\_module_name\InstalledComponents\SuphleIdentity\Payloads;

use Suphle\Services\Structures\ModelfulPayload;
use _database_namespace_\User;

class PasswordResetRequestBuilder extends ModelfulPayload
{
    protected function getBaseCriteria(): object
    {
        return User::where([
            'email' => $this->payloadStorage->getKey('email')
        ]);
    }

    protected function onlyFields(): array
    {
        return ['id', 'email'];
    }
}