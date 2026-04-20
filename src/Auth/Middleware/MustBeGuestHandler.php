<?php
namespace Suphle\Auth\Middleware;

use Suphle\Middleware\{BaseMiddleware, MiddlewareNexts};

use Suphle\Request\PayloadStorage;

use Suphle\Contracts\Presentation\BaseRenderer;

use Suphle\Exception\Explosives\UnexpectedAuthentication;

class MustBeGuestHandler extends BaseMiddleware
{
    use UserFinder;
    /**
     * @throws UnexpectedAuthentication
     */
    public function process(
        PayloadStorage $payloadStorage, 
        ?MiddlewareNexts $requestHandler
    ): BaseRenderer {

        if (!is_null($this->tryGetUserId())) throw new UnexpectedAuthentication();

        return $requestHandler->handle($payloadStorage);
    }
}