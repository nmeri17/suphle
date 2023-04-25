<?php

namespace Suphle\Contracts\Auth;

use Suphle\Contracts\Presentation\BaseRenderer;

interface LoginFlowMediator
{
    public function successRenderer(): BaseRenderer;

    public function failedRenderer(): BaseRenderer;

    public function getLoginService(): LoginActions;
}
