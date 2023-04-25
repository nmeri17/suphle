<?php

namespace Suphle\Tests\Integration\Auth\Bases;

use Suphle\Auth\{Renderers\BrowserLoginMediator, Repositories\BrowserAuthRepo};

use Suphle\Testing\TestTypes\ModuleLevelTest;

class BaseTestBrowserLoginMediator extends ModuleLevelTest
{
    use TestLoginMediator;

    final public const LOGIN_PATH = "/login";

    protected function loginRendererName(): string
    {

        return BrowserLoginMediator::class;
    }

    protected function loginRepoService(): string
    {

        return BrowserAuthRepo::class;
    }
}
