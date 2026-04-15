<?php
namespace Suphle\Auth\RequestScrutinizers;

use Suphle\Routing\Structures\BaseScrutinizerHandler;
use Suphle\Exception\Explosives\Unauthenticated;
use Suphle\Contracts\Auth\AuthStorage;

class AuthenticateHandler extends BaseScrutinizerHandler
{
    /**
     * @throws Unauthenticated
     */
    public function scrutinizeRequest(): void
    {
        // Get the funnel we added in MiddlewareQueue
        $funnel = end($this->metaFunnels); 
        $storage = $funnel->authStorage;

        if (is_null($storage->getId())) {
            throw new Unauthenticated($storage);
        }

        // Finalize the global binding for the rest of the request
        $this->container->whenTypeAny()
            ->needsAny([AuthStorage::class => $storage]);
    }
}