<?php

namespace Suphle\Auth\RequestScrutinizers;

use Suphle\Hydration\Container;

use Suphle\Routing\Structures\BaseScrutinizerHandler;

use Suphle\Services\Decorators\BindsAsSingleton;

use Suphle\Exception\Explosives\UnauthorizedServiceAccess;

#[BindsAsSingleton]
class PathAuthorizationScrutinizer extends BaseScrutinizerHandler {

    public function scrutinizeRequest(): void
    {
        if (!$this->passesActiveRules()) {
            throw new UnauthorizedServiceAccess();
        }
    }

    public function passesActiveRules(): bool
    {
        foreach ($this->metaFunnels as $funnel) {
            // Get the rule singleton and check permit
            if (!$this->container->getClass($funnel->ruleClass)->permit()) {
                return false;
            }
        }
        return true;
    }
}