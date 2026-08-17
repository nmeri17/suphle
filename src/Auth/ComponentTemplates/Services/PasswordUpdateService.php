<?php

namespace _modules_shell\_module_name\InstalledComponents\SuphleIdentity\Services;

use Suphle\Services\Structures\BaseErrorCatcherService;
use Suphle\Services\Decorators\{InterceptsCalls, DomainService, VariableDependencies};
use Suphle\Contracts\Services\CallInterceptors\SystemModelEdit;

#[InterceptsCalls(SystemModelEdit::class)]
#[VariableDependencies(["setPayloadStorage"])]
#[DomainService(mutation: true)]
class PasswordUpdateService implements SystemModelEdit
{
    use BaseErrorCatcherService;

    public function updateModels(object $resetToken): bool
    {
        $user = $resetToken->user;

        $updated = $user->update([
            'password' => password_hash(
                $this->payloadStorage->getKey('password'),
                PASSWORD_BCRYPT
            )
        ]);

        if ($updated) {
            $resetToken->delete();
        }

        return $updated;
    }

    public function modelsToUpdate(object $resetToken): array
    {
        return [$resetToken->user];
    }
}