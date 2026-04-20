<?php
namespace _modules_shell\_module_name\SuphleIdentity\Services;

use Suphle\Services\Structures\BaseErrorCatcherService;
use Suphle\Services\Decorators\{InterceptsCalls, VariableDependencies, DomainService};
use Suphle\Contracts\Services\CallInterceptors\SystemModelEdit;
use _database_namespace_\User;

#[InterceptsCalls(SystemModelEdit::class)]
#[VariableDependencies(["setPayloadStorage"])]
#[DomainService(mutation: true)]
class RegisterService implements SystemModelEdit {

    use BaseErrorCatcherService;

    public function updateModels(object $data): User {

        return User::create([
            "name" => $data->name,
            "email" => $data->email,
            "password" => password_hash($data->password, PASSWORD_BCRYPT)
        ]);
    }

    /**
     * The Handler passes the same $data object here, 
     * allowing for dynamic locking based on request input.
     */
    public function modelsToUpdate(object $data): array {

        return []; // Nothing to lock for registration
    }
}