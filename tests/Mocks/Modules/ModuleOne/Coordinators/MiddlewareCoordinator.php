<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Coordinators\ServiceCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod, PreMiddleware};
use Suphle\Response\Format\Json;
use Suphle\Tests\Mocks\Modules\ModuleOne\Middleware\AuthMiddleware;

class MiddlewareCoordinator extends ServiceCoordinator
{
    #[Route('/secure')]
    #[PreMiddleware(AuthMiddleware::class)]
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