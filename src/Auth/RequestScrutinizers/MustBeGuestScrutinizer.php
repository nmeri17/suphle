<?php

namespace Suphle\Auth\RequestScrutinizers;

use Suphle\Routing\Structures\BaseScrutinizerHandler;

use Suphle\Services\Decorators\BindsAsSingleton;

use Suphle\Exception\Explosives\UnexpectedAuthentication;

#[BindsAsSingleton]
class MustBeGuestScrutinizer extends BaseScrutinizerHandler
{
    public function __construct(
        protected readonly AuthenticateHandler $authScrutinizer
    ) {

        //
    }

    public function scrutinizeRequest(): void
    {
        $foundUser = null;

        try {

            $this->authScrutinizer->scrutinizeRequest();
        }
        catch (Unauthenticated $exception) {

            $foundUser = false;
        }
        if ($foundUser !== false)

            throw new UnexpectedAuthentication();
        
    }
}
