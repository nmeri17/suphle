<?php

namespace Suphle\Auth\RequestScrutinizers;

use Suphle\Hydration\Container;

use Suphle\Routing\Structures\BaseScrutinizerHandler;

use Suphle\Services\Decorators\BindsAsSingleton;

use Suphle\Exception\Explosives\UnauthorizedServiceAccess;

#[BindsAsSingleton]
class PathAuthorizationScrutinizer extends BaseScrutinizerHandler
{
    public function __construct(
        protected readonly Container $container
    ) {

        //
    }

    public function scrutinizeRequest(): void
    {

        if (!$this->passesActiveRules()) {

            throw new UnauthorizedServiceAccess();
        }
    }

    public function passesActiveRules(): bool
    {

        foreach ($this->metaFunnels as $funnel) {

            if (!$this->container->getClass($funnel->ruleClass)->permit()) {

                return false;
            }
        }

        return true;
    }
}
