<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\{BaseCoordinator, Decorators\ValidationRules};
use Suphle\Routing\Attributes\{Route, HttpMethod, RoutePrefix};
use Suphle\Response\Format\{Json, Markup, Redirect};

#[RoutePrefix('/validators')]
class ValidatorCoordinator extends BaseCoordinator
{
    #[Route("get-without", HttpMethod::GET)]
    public function handleGet(): Markup
    {
        return new Markup("secure-some.edit-form", ["message" => "mercy"]);
    }

    #[Route("post-without", HttpMethod::POST)]
    public function postNoValidator(): Json
    {
        return new Json([]);
    }

    #[Route("post-with-json", HttpMethod::POST)]
    #[ValidationRules(["foo" => "required"])]
    public function postWithValidator(): Json
    {
        return new Json([]);
    }

    #[Route("post-with-html", HttpMethod::POST)]
    #[ValidationRules(["foo" => "required"])]
    public function postWithValidatorRedirect(): Redirect
    {
        return new Redirect(fn () => "/");
    }
}
