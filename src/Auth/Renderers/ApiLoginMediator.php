<?php

namespace Suphle\Auth\Renderers;

use Suphle\Contracts\Auth\{LoginFlowMediator, LoginActions};

use Suphle\Contracts\Presentation\BaseRenderer;

use Suphle\Response\Format\Json;

use Suphle\Auth\Repositories\ApiAuthRepo;

class ApiLoginMediator implements LoginFlowMediator
{
    public function __construct(protected readonly ApiAuthRepo $authService)
    {
        //
    }

    public function successRenderer(): BaseRenderer
    {
        return new Json([
            'status' => 'success',
            'message' => 'Login successful'
        ]);
    }

    public function failedRenderer(): BaseRenderer
    {
        return new Json([
            'status' => 'error',
            'message' => 'Login failed'
        ]);
    }

    public function getLoginService(): LoginActions
    {
        return $this->authService;
    }
}
