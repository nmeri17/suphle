<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Coordinators\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod};
use Suphle\Response\Format\Json;
use Suphle\Tests\Mocks\Modules\ModuleOne\Middleware\AuthMiddleware;

class MiddlewareCoordinator extends BaseCoordinator
{
    #[Route('/secure', method: HttpMethod::GET, middlewares: [AuthMiddleware::class])]
    public function secureRoute(): Json
    {
        return new Json(['secure' => true, 'message' => 'Access granted']);
    }

    #[Route('/public', method: HttpMethod::GET)]
    public function publicRoute(): Json
    {
        return new Json(['public' => true, 'message' => 'No auth required']);
    }
} 