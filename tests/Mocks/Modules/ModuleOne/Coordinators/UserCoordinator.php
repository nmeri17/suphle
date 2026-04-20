<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Routing\Attributes\{Route, RoutePrefix, CanaryRoute, HttpMethod, CanaryState, PreMiddleware};
use Suphle\Response\Format\{Json, Redirect, Reload, Markup};
use Suphle\Tests\Mocks\Modules\ModuleOne\Middleware\AuthMiddleware;
use Suphle\Tests\Mocks\Modules\ModuleOne\Canary\{BetaUserCanary, SpecialUserCanary};
use Suphle\Coordinators\ServiceCoordinator;
use Suphle\Auth\RequestScrutinizers\AuthenticateHandler;

#[CanaryState([BetaUserCanary::class, SpecialUserCanary::class])]
#[RoutePrefix('api/v1/users')]
class UserCoordinator extends ServiceCoordinator
{
    #[Route('/')]
    #[PreMiddleware(AuthenticateHandler::class)]
    public function index(): Json
    {
        $canary = $this->requestDetails->getCanaryState();
        return match ($canary) {
            'beta'    => new Json(['users' => ['Jane', 'John'], 'flag' => 'BETA']),
            'special' => new Json(['users' => ['Jane', 'John'], 'flag' => 'SPECIAL']),
            default   => new Json(['users' => ['Jane', 'John'], 'flag' => 'STABLE']),
        };
    }

    #[Route('/', method: HttpMethod::POST)]
    public function store(): Json
    {
        return new Json(['status' => 'created']);
    }

    #[Route('/secure')]
    #[PreMiddleware(AuthenticateHandler::class)]
    public function secure(): Json
    {
        return new Json(['message' => 'Secure content']);
    }

    #[Route('/{id}')]
    public function show(): Json
    {
        return new Json(['message' => 'User details']);
    }

    #[Route('/{id}', method: HttpMethod::PUT)]
    public function update(): Json
    {
        $id = $this->pathPlaceholders->getSegmentValue('id');
        return new Json(['status' => 'updated', 'id' => $id]);
    }

    #[Route('/{id}', method: HttpMethod::DELETE)]
    public function destroy(): Json
    {
        $id = $this->pathPlaceholders->getSegmentValue('id');
        return new Json(['status' => 'deleted', 'id' => $id]);
    }

    #[Route('/redirect-example', method: HttpMethod::POST)]
    public function redirectExample(): Redirect
    {
        // Example of redirect with callback logic
        return new Redirect(function() {
            // Logic to determine redirect destination
            return '/api/v1/users';
        });
    }

    #[Route('/reload-example', method: HttpMethod::PUT)]
    public function reloadExample(): Reload
    {
        // Example of reload response - framework handles validation data automatically
        return new Reload();
    }

    #[Route('/markup-example')]
    public function markupExample(): Markup
    {
        return new Markup('test.markup', ['data' => 'example data']);
    }
} 