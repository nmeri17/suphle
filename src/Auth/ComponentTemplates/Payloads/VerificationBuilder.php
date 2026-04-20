<?php
namespace _modules_shell\_module_name\SuphleIdentity\Payloads;

use Suphle\Services\Structures\ModelfulPayload;

use _database_namespace_\User;

class VerificationBuilder extends ModelfulPayload {

    protected const TOKEN_COL = "verification_token";

    protected function getBaseCriteria(): object
    {
        $token = $this->routeInfo->getSegmentValue("token") ?? // web vs api
        
        $this->payloadStorage->getKey("token");

        return User::where([static::TOKEN_COL => $token]);
    }

    protected function onlyFields(): array
    {
        return ["id", static::TOKEN_COL];
    }
}