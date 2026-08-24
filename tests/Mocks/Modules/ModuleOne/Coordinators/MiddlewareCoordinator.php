<?php
namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\{BaseCoordinator, Decorators\ValidationRules};
use Suphle\Routing\Attributes\{Route, HttpMethod, Middleware, RoutePrefix, ClearMiddleware};
use Suphle\Response\Format\Json;
use Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares\{BlankMiddlewareHandler};

#[RoutePrefix("/middleware")]
#[Middleware(BlankMiddlewareHandler::class)]
class MiddlewareCoordinator extends BaseCoordinatory {

    #[Route("/segment")]
    public function plainSegment(): Json
    {
        return new Json(["message" => "plain Segment"]);
    }

    #[Route("secede")]
    #[ClearMiddleware(BlankMiddlewareHandler::class)]
    public function secede(): Json
    {
        return new Json([]);
    }
}