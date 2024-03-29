<?php

namespace Suphle\Contracts\Services\Models;

use Suphle\Contracts\Auth\AuthStorage;

use DateTime;

interface IntegrityModel
{
    public const INTEGRITY_COLUMN = "updated_at"; // Migration should create this column for methods to read from

    /**
     * If [INTEGRITY_COLUMN] is behind now, user is looking at a stale version
    */
    public function includesEditIntegrity(string $integrity): bool;

    /**
     * Unset all integrities for this model
    */
    public function nullifyEditIntegrity(DateTime $integrity): void;

    public function enableAudit(): bool;

    public function makeHistory(AuthStorage $authStorage, $payload): void;
}
