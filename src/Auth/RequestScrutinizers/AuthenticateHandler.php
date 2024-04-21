<?php

namespace Suphle\Auth\RequestScrutinizers;

use Suphle\Hydration\Container;

use Suphle\Routing\{PatternIndicator, Structures\BaseScrutinizerHandler};

use Suphle\Contracts\{Auth\AuthStorage, Config\Router as RouterConfig};

use Suphle\Exception\Explosives\Unauthenticated;

class AuthenticateHandler extends BaseScrutinizerHandler
{
    public function __construct(
        protected readonly Container $container,
        protected readonly PatternIndicator $patternIndicator,
        protected readonly RouterConfig $routerConfig
    ) {

        //
    }

    /**
     * It'll override the default authStorage method provided
     *
     * @throws Unauthenticated
    */
    public function scrutinizeRequest(): void
    {

        if ($this->patternIndicator->shouldMirror()) {

            $routedMechanism = $this->container->getClass(
                $this->routerConfig->mirrorAuthenticator()
            );
        } else {
            $routedMechanism = end($this->metaFunnels)->authStorage; // {end} means we just work with the last attached AuthenticateMetaFunnel since it's most relevant
        }

        if (is_null($routedMechanism->getId())) {

            throw new Unauthenticated($routedMechanism);
        }

        $this->container->whenTypeAny()

        ->needsAny([ AuthStorage::class => $routedMechanism]);
    }
}
