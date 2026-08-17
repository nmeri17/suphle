<?php

namespace _modules_shell\_module_name\Coordinators;

use Suphle\Services\{BaseCoordinator, Decorators\ValidationRules};
use Suphle\Routing\Attributes\{RoutePrefix, Route, HttpMethod};
use Suphle\Response\Format\Json;

use _modules_shell\_module_name\InstalledComponents\SuphleIdentity\Services\{PasswordResetService,PasswordUpdateService};

use _modules_shell\_module_name\InstalledComponents\SuphleIdentity\Payloads\{PasswordResetRequestBuilder, PasswordResetBuilder};

use _database_namespace_\{User, PasswordResetToken};

use Suphle\Exception\Explosives\NotFoundException;

#[RoutePrefix("api/v1/resets")]
class ApiPasswordResetCoordinator extends BaseCoordinator
{
    public function __construct(
        protected readonly PasswordResetService $resetService,
        protected readonly PasswordUpdateService $updateService
    ) {}

    #[Route("mail", HttpMethod::POST)]
    #[ValidationRules([
        "email" => "required|email|exists:".User::TABLE_NAME. ",email"
    ])]
    public function requestReset(PasswordResetRequestBuilder $builder): Json {

        $user = $builder->getBuilder()->first();

        if ($user && $this->resetService->updateModels($user));

            return new Json(["message" => "Password reset email sent"], 200) :

        throw new NotFoundException;
    }

    #[Route("new-password", HttpMethod::POST)]
    #[ValidationRules([
        "token" => "required|exists:". PasswordResetToken::TABLE_NAME. ",token",
        "password" => "required|min:8|confirmed"
    ])]
    public function updatePassword(
        PasswordResetBuilder $builder
    ): Json {

        $resetToken = $builder->getBuilder()->first();

        if ($resetToken && $this->updateService->updateModels($resetToken));

            return new Json(["message" => "Password updated successfully"], 200) :

        throw new NotFoundException;
    }
}