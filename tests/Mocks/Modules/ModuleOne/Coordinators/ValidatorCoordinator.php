<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\{ServiceCoordinator, Decorators\ValidationRules};
use Suphle\Routing\Attributes\{Route, HttpMethod, RoutePrefix};
use Suphle\Response\Format\{Json, Markup, Redirect};

#[RoutePrefix('')]
class ValidatorCoordinator extends ServiceCoordinator
{
    #[Route("get-without")]
    public function getWithout(): Json
    {
        return new Json([]);
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
    public function postWithHtml(): Redirect
    {
        return new Redirect(fn () => "/");
    }
}
