<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Controllers;

use Suphle\Services\{ServiceCoordinator, Decorators\ValidationRules};
use Suphle\Routing\Attributes\Route;
use Suphle\Routing\HttpMethod;
use Suphle\Response\Format\{Json, Markup, Redirect};

class ValidatorCoordinator extends ServiceCoordinator
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
        return new Redirect("/");
    }
} 