<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Coordinators\ServiceCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};
use Suphle\Response\Format\{Json, Redirect, Reload, Markup};

#[RoutePrefix('api/v1/test')]
class TestCoordinator extends ServiceCoordinator
{
    #[Route('/')]
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
        return new Reload();
    }

    #[Route('/markup-example')]
    public function markupExample(): Markup
    {
        return new Markup('test.markup', ['data' => 'example data']);
    }
} 