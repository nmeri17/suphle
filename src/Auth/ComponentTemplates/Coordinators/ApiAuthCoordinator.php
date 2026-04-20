<?php
namespace _modules_shell\_module_name\SuphleIdentity\Coordinators;

use Suphle\Services\{BaseCoordinator, Decorators\ValidationRules};
use Suphle\Routing\Attributes\{RoutePrefix, Route, HttpMethod};
use Suphle\Contracts\Presentation\BaseRenderer;
use Suphle\Response\Format\Json;
use Suphle\Auth\BaseAuthService;
use _modules_shell\_module_name\SuphleIdentity\Services\{RegisterService, VerificationService};
use _modules_shell\_module_name\SuphleIdentity\Payloads\{RegistrationReader, VerificationBuilder};

#[RoutePrefix("api/v1/auth")]
class ApiAuthCoordinator extends BaseCoordinator {

    public function __construct(
        protected readonly BaseAuthService $loginService,
        protected readonly RegisterService $registerService,
        protected readonly VerificationService $verificationService
    ) {}

    #[Route("login", HttpMethod::POST)]
    #[ValidationRules([
        "email" => "required|email",
        "password" => "required"
    ])]
    public function apiLogin(): Json {

        $token = $this->authService->tryGetJsonToken();

        return $token ?
            new Json(["token" => $token]) :
            new Json(["error" => "Unauthorized"], 401);
    }

    #[Route("register", HttpMethod::POST)]
    #[ValidationRules([
        "name" => "required",
        "email" => "required|email|unique:users,email",
        "password" => "required|min:8"
    ])]
    public function apiRegister(RegistrationReader $reader): Json {

        return $this->registerService->updateModels((object) $reader->getDomainObject()) ?

            new Json(["status" => "success"], 201) : 
            new Json(["error" => "Registration failed"], 400);
    }

    #[Route("verify-email/", HttpMethod::POST)]
    #[ValidationRules([
        "token" => "required|string|size:64"
    ])]
    public function apiVerifyEmail(VerificationBuilder $builder): BaseRenderer {

        $user = $builder->getBuilder()->first();

        if (!$user) return new Json(["error" => "Invalid verification token"], 404);

        $status = $this->verificationService->updateModels($user);

        return $status ?
            new Json(["message" => "Email verified successfully"], 200) : 
            new Json(["error" => "Invalid or expired token"], 400);
    }
}