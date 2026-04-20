<?php
namespace Suphle\Auth\Middleware;

use Suphle\Middleware\{BaseMiddleware, MiddlewareNexts};

use Suphle\Request\PayloadStorage;

use Suphle\Contracts\{Presentation\BaseRenderer, Auth\AuthStorage};

use Suphle\Exception\Explosives\Unauthenticated;

class AuthenticateHandler extends BaseMiddleware
{
    use UserFinder;
    /**
     * @throws Unauthenticated
     */
    public function process(
        PayloadStorage $payloadStorage, 
        ?MiddlewareNexts $requestHandler
    ): BaseRenderer {

        if (is_null($this->tryGetUserId())) throw new Unauthenticated($this->storage);

        /**
         * Finalize the global binding. This ensures that any service 
         * requiring AuthStorage gets this specific hydrated instance.
         */
        $this->container->whenTypeAny()

        ->needsAny([AuthStorage::class => $this->storage]);

        return $requestHandler->handle($payloadStorage);
    }
}