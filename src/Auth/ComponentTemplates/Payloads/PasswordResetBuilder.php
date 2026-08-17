<?php

namespace _modules_shell\_module_name\InstalledComponents\SuphleIdentity\Payloads;

use Suphle\Services\Structures\ModelfulPayload;
use _database_namespace_\PasswordResetToken;

class PasswordResetBuilder extends ModelfulPayload
{
    protected function getBaseCriteria(): object
    {
        return PasswordResetToken::where([
            'token' => $this->routeInfo->getSegmentValue('token')
                ?? $this->payloadStorage->getKey('token')
        ])->with('user');
    }

    protected function onlyFields(): array
    {
        return ['id', 'token', 'user_id'];
    }
}