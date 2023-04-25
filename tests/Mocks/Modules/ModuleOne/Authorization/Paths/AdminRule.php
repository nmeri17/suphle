<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Authorization\Paths;

use Suphle\Request\RouteRule;

class AdminRule extends RouteRule
{
    public function permit(): bool
    {

        return $this->authStorage->getUser()->isAdmin();
    }
}
