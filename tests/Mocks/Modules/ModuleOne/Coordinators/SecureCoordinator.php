<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod, PreMiddleware, ClearMiddleware};
use Suphle\Response\Format\{Json, Markup};
use Suphle\Auth\Middleware\{AuthenticateHandler, MustBeGuestHandler};

#[RoutePrefix("/secure", mirrorPrefix: "api/v1")]
#[PreMiddleware(AuthenticateHandler::class)]
class SecureCoordinator extends BaseCoordinator
{
    #[Route("dashboard")]
    public function dashboard(): Markup
    {
        return new Markup("generic.default", ["user" => "service returning user"]);
    }

    #[Route("data")]
    public function apiData(): Json
    {
        return new Json(["data" => "secure data"]);
    }

    #[Route("public")]
    #[ClearMiddleware(AuthenticateHandler::class)]
    public function publicEndpoint(): Json
    {
        return new Json(["message" => "public data"]);
    }

    #[Route("strictly-guest")]
    #[ClearMiddleware(AuthenticateHandler::class)]
    #[PreMiddleware(MustBeGuestHandler::class)]
    public function guestEndpoint(): Json
    {
        return new Json(["message" => "guest only"]);
    }
} 