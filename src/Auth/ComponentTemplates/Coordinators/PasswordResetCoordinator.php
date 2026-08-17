<?php

namespace _modules_shell\_module_name\Coordinators;

use Suphle\Services\{BaseCoordinator, Decorators\ValidationRules};
use Suphle\Response\Format\{Redirect, Reload, Markup};
use Suphle\Routing\Attributes\{RoutePrefix, Route, HttpMethod};
use Suphle\Contracts\Presentation\BaseRenderer;
use Suphle\Security\CSRF\CsrfGenerator;
use _modules_shell\_module_name\InstalledComponents\SuphleIdentity\Services\{PasswordResetService, PasswordUpdateService};
use _modules_shell\_module_name\InstalledComponents\SuphleIdentity\Payloads\{PasswordResetRequestBuilder, PasswordResetBuilder};
use _database_namespace_\{User, PasswordResetToken};
use Suphle\Exception\Explosives\NotFoundException;

#[RoutePrefix("resets")]
class PasswordResetCoordinator extends BaseCoordinator
{

    public function __construct(
        protected readonly PasswordResetService $resetService,
        protected readonly PasswordUpdateService $updateService,
        protected readonly CsrfGenerator $csrf
    ) {}

    #[Route("")]
    public function showRequestForm(): BaseRenderer
    {
        return new Markup("auth.resets.request", $this->copyValidationErrors([
            "csrf_token" => $this->csrf->newToken()
        ]));
    }

    #[Route("mail", HttpMethod::POST)]
    #[ValidationRules([
        "email" => "required|email|exists:". User::TABLE_NAME. ",email"
    ])]
    public function sendResetMail(PasswordResetRequestBuilder $builder): BaseRenderer
    {
        $user = $builder->getBuilder()->first();

        if (!$user) return new Reload();

        $this->resetService->updateModels($user);

        return new Redirect(fn () => "/resets/mail-success");
    }

    #[Route("mail-success")]
    public function mailSuccess(): BaseRenderer
    {
        return new Markup("auth.resets.mail-success");
    }

    #[Route("confirm-reset/{token}")]
    public function showResetForm(PasswordResetBuilder $builder): BaseRenderer
    {
        $resetToken = $builder->getBuilder()->first();

        if (!$resetToken) throw new NotFoundException;

        return new Markup("auth.resets.confirm-reset", [
            "csrf_token" => $this->csrf->newToken(),
            "token"      => $resetToken->token
        ]);
    }

    #[Route("new-password", HttpMethod::POST)]
    #[ValidationRules([
        "token"                 => "required|exists:". PasswordResetToken::TABLE_NAME. ",token",
        "password"              => "required|min:8|confirmed",
        "password_confirmation" => "required"
    ])]
    public function updatePassword(PasswordResetBuilder $builder): BaseRenderer
    {
        $resetToken = $builder->getBuilder()->first();

        if ($resetToken && $this->updateService->updateModels($resetToken)) 

            return new Redirect(fn () => "/resets/password-updated");
        
        throw new NotFoundException;
    }

    #[Route("password-updated")]
    public function passwordUpdated(): BaseRenderer
    {
        return new Markup("auth.resets.password-updated");
    }
}