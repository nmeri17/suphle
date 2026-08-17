<?php
namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod, PreMiddleware, ClearMiddleware};
use Suphle\Response\Format\{Json, Markup};
use Suphle\Auth\Middleware\AuthenticateHandler;

#[RoutePrefix("/secure", mirrorPrefix: "api/v2")]
class SecureCoordinatorV2 extends SecureCoordinator {

    #[Route("data")]
    public function apiData(): Json
    {
        return new Json(["data" => "v2"]);
    }

    #[Route("unlink")]
    #[ClearMiddleware(AuthenticateHandler::class)]
    public function unlinkRoute(): Json
    {
        return new Json([]);
    }
}