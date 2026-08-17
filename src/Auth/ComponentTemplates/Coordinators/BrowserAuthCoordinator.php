<?php
namespace _modules_shell\_module_name\Coordinators;

use Suphle\Services\{BaseCoordinator, Decorators\ValidationRules};
use Suphle\Response\Format\{Redirect, Reload, Markup};
use Suphle\Routing\Attributes\{RoutePrefix, Route, HttpMethod};
use Suphle\Contracts\Presentation\BaseRenderer;
use Suphle\Security\CSRF\CsrfGenerator;
use Suphle\Auth\BaseAuthService;
use _modules_shell\_module_name\InstalledComponents\SuphleIdentity\Services\{RegisterService, VerificationService};
use _modules_shell\_module_name\InstalledComponents\SuphleIdentity\Payloads\{RegistrationReader, VerificationBuilder};

use _database_namespace_\{User, PasswordResetToken};
use Suphle\Exception\Explosives\NotFoundException;

#[RoutePrefix("/auth")]
class BrowserAuthCoordinator extends BaseCoordinator {

    public function __construct(
        protected readonly BaseAuthService $loginService,
        protected readonly RegisterService $registerService,
        protected readonly VerificationService $verificationService,
        protected readonly CsrfGenerator $csrf
    ) {}

    #[Route("login")]
    public function showLogin(): BaseRenderer {

        return new Markup("auth.login", $this->copyValidationErrors([

            "csrf_token" => $this->csrf->newToken()
        ]));
    }

    #[Route("login", HttpMethod::POST)]
    #[ValidationRules([
        "email" => "required|email",
        "password" => "required|min:8"
    ])]
    public function handleLogin(): BaseRenderer {

        return match ($this->loginService->tryStartUserSession()) {
            null => new Reload(),

            default => new Redirect($this->loginService->successRedirect("/dashboard"))
        };
    }

    #[Route("register")]
    public function showRegister(): BaseRenderer {

        return new Markup("auth.register", $this->copyValidationErrors([

            "csrf_token" => $this->csrf->newToken()
        ]));
    }

    #[Route("register", HttpMethod::POST)]
    #[ValidationRules([
        "name" => "required|string",
        "email" => "required|email|unique:". User::TABLE_NAME. ",email",
        "password" => "required|min:8|confirmed"
    ])]
    public function handleRegister(RegistrationReader $reader): BaseRenderer {

        return $this->registerService->updateModels((object) $reader->getDomainObject()) ? 

            new Redirect($this->loginService->successRedirect("/check-verify-mail")): // or dashboard

            new Reload();
    }

    #[Route("register-complete")]
    public function registrationComplete(): BaseRenderer {

        return new Markup("auth.register-complete");
    }
    #[Route("check-verify-mail")]
    public function showVerificationNotice(): BaseRenderer {

        return new Markup("auth.check-verify-mail");
    }

    #[Route("verify-email/{token}")]
    public function verifyEmail(VerificationBuilder $builder): BaseRenderer {

        $user = $builder->getBuilder()->first();

        // Calling updateModels ON THE PROXY triggers the transaction and lock
        if ($user && $this->verificationService->updateModels($user)) 
            
            return new Redirect($this->loginService->authRequiredUrl(...));

        throw new NotFoundException;
    }

    #[Route("logout", HttpMethod::POST)]
    public function logout(): BaseRenderer {

        $this->loginService->logout();

        return new Redirect($this->loginService->authRequiredUrl(...));
    }
}