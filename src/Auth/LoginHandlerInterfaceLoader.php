<?php

namespace Suphle\Auth;

use Suphle\Hydration\{BaseInterfaceLoader, Container};

use Suphle\Contracts\{Config\AuthContract, Auth\LoginFlowMediator};

class LoginHandlerInterfaceLoader extends BaseInterfaceLoader
{
    public function __construct(
        protected readonly Container $container,
        protected readonly AuthContract $authContract
    ) {

        //
    }

    public function concreteName(): string
    {

        return LoginRequestHandler::class;
    }

    public function bindArguments(): array
    {

        return [

            LoginFlowMediator::class => $this->container->getClass(
                $this->authContract->getLoginCollection()
            ) // passing collection as argument so the handler can receive a type-safe object
        ];
    }
}
