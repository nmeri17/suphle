<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Coordinators\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};
use Suphle\Response\Format\{Json, Redirect, Reload, Markup};

#[RoutePrefix('api/v1/test')]
class TestCoordinator extends BaseCoordinator
{
    #[Route('/', method: HttpMethod::GET)]
    public function index(): Json
    {
        return new Json(['users' => ['Jane', 'John']]);
    }

    #[Route('/', method: HttpMethod::POST)]
    public function store(): Json
    {
        return new Json(['status' => 'created']);
    }

    #[Route('/redirect-example', method: HttpMethod::POST)]
    public function redirectExample(): Redirect
    {
        return new Redirect(function() {
            return '/api/v1/users';
        });
    }

    #[Route('/reload-example', method: HttpMethod::PUT)]
    public function reloadExample(): Reload
    {
        // Reload should receive data for validation errors and old input
        return new Reload([
            'validation_errors' => ['field' => 'Error message'],
            'payload_storage' => ['old_field' => 'old_value']
        ]);
    }

    #[Route('/markup-example', method: HttpMethod::GET)]
    public function markupExample(): Markup
    {
        return new Markup('user-profile');
    }
} 