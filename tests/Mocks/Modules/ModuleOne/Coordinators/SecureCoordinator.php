<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\ServiceCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod, PreMiddleware};
use Suphle\Response\Format\{Json, Markup};
use Suphle\Auth\RequestScrutinizers\AuthenticateMetaFunnel;

#[RoutePrefix('secure')]
#[PreMiddleware(AuthenticateMetaFunnel::class)]
class SecureCoordinator extends ServiceCoordinator
{
    #[Route('dashboard')]
    public function dashboard(): Markup
    {
        return new Markup('secure.dashboard', ['user' => $this->authStorage->getUser()]);
    }

    #[Route('api/data')]
    public function apiData(): Json
    {
        return new Json(['data' => 'secure data']);
    }

    #[Route('public')]
    #[PreMiddleware(null)] // Override class-level middleware
    public function publicEndpoint(): Json
    {
        return new Json(['message' => 'public data']);
    }
} 