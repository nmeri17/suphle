<?php
namespace _modules_shell\_module_name\SuphleIdentity\Coordinators;

use Suphle\Services\{BaseCoordinator, Decorators\ValidationRules};
use Suphle\Response\Format\{Redirect, Reload, Markup};
use Suphle\Routing\Attributes\{RoutePrefix, Route, HttpMethod};
use Suphle\Contracts\Presentation\BaseRenderer;
use Suphle\Security\CSRF\CsrfGenerator;
use Suphle\Auth\BaseAuthService;
use _modules_shell\_module_name\SuphleIdentity\Services\{RegisterService, VerificationService};
use _modules_shell\_module_name\SuphleIdentity\Payloads\{RegistrationReader, VerificationBuilder};

#[RoutePrefix("auth")]
class BrowserAuthCoordinator extends BaseCoordinator {

    public function __construct(
        protected readonly BaseAuthService $loginService,
        protected readonly RegisterService $registerService,
        protected readonly VerificationService $verificationService,
        protected readonly CsrfGenerator $csrf
    ) {}

    #[Route("login", HttpMethod::GET)]
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

    #[Route("register", HttpMethod::GET)]
    public function showRegister(): BaseRenderer {

        return new Markup("auth.register", $this->copyValidationErrors([

            "csrf_token" => $this->csrf->newToken()
        ]));
    }

    #[Route("register", HttpMethod::POST)]
    #[ValidationRules([
        "name" => "required|string",
        "email" => "required|email|unique:users,email",
        "password" => "required|min:8|confirmed"
    ])]
    public function handleRegister(RegistrationReader $reader): BaseRenderer {

        return $this->registerService->updateModels((object) $reader->getDomainObject()) ? 

            new Redirect($this->loginService->successRedirect("/dashboard")) :

            new Reload();
    }

    #[Route("verify-email/{token}", HttpMethod::GET)]
    public function verifyEmail(VerificationBuilder $builder): BaseRenderer {

        $user = $builder->getBuilder()->first();

        $notFound = new Redirect(fn () => "/404");

        if (!$user) return $notFound;

        // Calling updateModels ON THE PROXY triggers the transaction and lock
        $this->verificationService->updateModels($user)? 
            new Redirect($this->loginService->authRequiredUrl(...)) : 

            $notFound;
    }

    #[Route("logout", HttpMethod::POST)]
    public function logout(): BaseRenderer {

        $this->loginService->logout();

        return new Redirect($this->loginService->authRequiredUrl(...));
    }
}