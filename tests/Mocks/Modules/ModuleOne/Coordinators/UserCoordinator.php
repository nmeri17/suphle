<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Coordinators\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix, CanaryRoute, HttpMethod, CanaryState};
use Suphle\Response\Format\{Json, Redirect, Reload, Markup};
use Suphle\Tests\Mocks\Modules\ModuleOne\Middleware\AuthMiddleware;
use Suphle\Tests\Mocks\Modules\ModuleOne\Canary\{BetaUserCanary, SpecialUserCanary};

#[CanaryState([BetaUserCanary::class, SpecialUserCanary::class])]
#[RoutePrefix('api/v1/users')]
class UserCoordinator extends BaseCoordinator
{
    #[Route('/', method: HttpMethod::GET, middlewares: [AuthMiddleware::class])]
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

    #[Route('/secure', method: HttpMethod::GET, middlewares: [AuthMiddleware::class])]
    #[CanaryRoute([BetaUserCanary::class], FallbackForAllUsers::class)]
    public function secureRoute(): Json
    {
        return new Json(['beta' => true]);
    }

    #[Route('/{id}', method: HttpMethod::GET)]
    public function show(): Json
    {
        $id = $this->pathPlaceholders->getSegmentValue('id');
        return new Json(['user' => ['id' => $id, 'name' => 'John Doe']]);
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
        // Example of reload response with validation errors and old input
        return new Reload([
            'validation_errors' => ['email' => 'Invalid email format'],
            'payload_storage' => ['email' => 'user@example.com']
        ]);
    }

    #[Route('/markup-example', method: HttpMethod::GET)]
    public function markupExample(): Markup
    {
        // Example of markup response
        return new Markup('user-profile');
    }
} 