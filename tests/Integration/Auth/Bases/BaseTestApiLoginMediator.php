<?php

namespace Suphle\Tests\Integration\Auth\Bases;

use Suphle\Auth\{Renderers\ApiLoginMediator, Repositories\ApiAuthRepo};

use Suphle\Testing\TestTypes\ModuleLevelTest;

class BaseTestApiLoginMediator extends ModuleLevelTest
{
    use TestLoginMediator;

    public const LOGIN_PATH = "/api/v1/login";

    protected function loginRendererName(): string
    {

        return ApiLoginMediator::class;
    }

    protected function loginRepoService(): string
    {

        return ApiAuthRepo::class;
    }
}
