<?php
namespace _modules_shell\_module_name\InstalledComponents\SuphleIdentity\Services;

use Suphle\Services\Structures\BaseSystemModelEdit;
use _database_namespace\User; // auto-ejected during project init
use DateTimeImmutable;

class VerificationService extends BaseSystemModelEdit {

    public function updateModels(object $user): bool {

        if (!is_null($user->email_verified_at)) return false;

        return $user->update([

            "verification_token" => null,

            "email_verified_at" => (new DateTimeImmutable())->format("Y-m-d H:i:s")
        ]);
    }

    /**
     * The Handler passes the same $data object here, 
     * allowing for dynamic locking based on request input.
     */
    public function modelsToUpdate(object $user): array {

        return [$user]; // Locked by the handler BEFORE updateModels runs
    }
}