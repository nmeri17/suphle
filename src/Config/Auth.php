<?php

namespace Suphle\Config;

use Suphle\Contracts\Config\AuthContract;

use Suphle\Auth\Renderers\{BrowserLoginMediator, ApiLoginMediator}; // just make these backing services that can be used in controllers

class Auth implements AuthContract
{
    final public const API_LOGIN_PATH = "api/v1/login";

    public function getModelObservers(): array
    {

        return [];
    }

    public function markupRedirect(): string
    {

        return "login";
    }
}
