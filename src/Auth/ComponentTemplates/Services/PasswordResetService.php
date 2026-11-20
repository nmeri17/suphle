<?php
namespace _modules_shell\_module_name\InstalledComponents\SuphleIdentity\Services;

use Suphle\Services\Structures\BaseSystemModelEdit;
use Suphle\Events\{EmitProxy, EventPropagator};
use _database_namespace\{User, PasswordResetToken};

class PasswordResetService extends BaseSystemModelEdit {
    use EmitProxy;

    public const RESET_REQUESTED = "password_reset_requested";

    public function __construct(
        protected readonly EventPropagator $eventEmitter
    ) {}

    public function updateModels(object $user): bool
    {
        PasswordResetToken::where("user_id", $user->id)->delete();

        $token = PasswordResetToken::create([
            "user_id" => $user->id,
            "token"   => bin2hex(random_bytes(32))
        ]);

        $this->emitHelper(self::RESET_REQUESTED, [
            "email" => $user->email,
            "token" => $token->token
        ]);

        return true;
    }

    public function modelsToUpdate(object $user):iterable
    {
        return [$user];
    }
}