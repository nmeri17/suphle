<?php
namespace _modules_shell\_module_name\InstalledComponents\SuphleIdentity\Services;

use Suphle\Services\Structures\BaseErrorCatcherService;
use Suphle\Services\Decorators\{InterceptsCalls, VariableDependencies, DomainService};
use Suphle\Contracts\{Events, Services\CallInterceptors\SystemModelEdit};

use Suphle\Events\EmitProxy;

use _database_namespace_\User;

#[InterceptsCalls(SystemModelEdit::class)]
#[VariableDependencies(["setPayloadStorage"])]
#[DomainService(mutation: true)]
class RegisterService implements SystemModelEdit {

    use BaseErrorCatcherService, EmitProxy;

    public const USER_REGISTERED = "user_registered";

    public function __construct(
        protected readonly Events $eventManager
    ) {}

    public function updateModels(object $data): User {

        $user = User::create([
            "name" => $data->name,
            "email" => $data->email,
            "password" => password_hash($data->password, PASSWORD_BCRYPT),
            "verification_token" => bin2hex(random_bytes(32))
        ]);
        $this->emitHelper(self::USER_REGISTERED, [
            "email" => $user->email,
            "verification_token" => $user->verification_token
        ]);

        return $user;
    }

    /**
     * The Handler passes the same $data object here, 
     * allowing for dynamic locking based on request input.
     */
    public function modelsToUpdate(object $data): array {

        return []; // Nothing to lock for registration
    }
}