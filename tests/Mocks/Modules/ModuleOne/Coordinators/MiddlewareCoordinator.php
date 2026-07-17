<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod, PreMiddleware, RoutePrefix};
use Suphle\Response\Format\Json;
use Suphle\Auth\Middleware\AuthenticateHandler;

#[RoutePrefix("/middleware")]
class MiddlewareCoordinator extends BaseCoordinator
{
    #[Route('/secure')]
    #[PreMiddleware(AuthenticateHandler::class)]
    public function secure(): Json
    {
        return new Json(['message' => 'Secure content']);
    }

    #[Route('/public')]
    public function public(): Json
    {
        return new Json(['message' => 'Public content']);
    }
} 