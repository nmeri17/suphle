<?php
namespace Suphle\Auth\Middleware;

use Suphle\Middleware\{BaseMiddleware, MiddlewareNexts};

use Suphle\Request\PayloadStorage;

use Suphle\Services\Decorators\BindsAsSingleton;

use Suphle\Contracts\Presentation\BaseRenderer;

use Suphle\Exception\Explosives\UnauthorizedServiceAccess;

#[BindsAsSingleton]
class PathAuthorization extends BaseMiddleware
{
    /**
     * @throws UnauthorizedServiceAccess
     */
    public function process(
        PayloadStorage $payloadStorage, 
        ?MiddlewareNexts $requestHandler
    ): BaseRenderer {

        if (!$this->passesActiveRules()) throw new UnauthorizedServiceAccess();

        return $requestHandler->handle($payloadStorage);
    }

    public function passesActiveRules(): bool
    {
        foreach ($this->args as $ruleClass) {
            // Get the rule singleton and check permit
            if (!$this->container->getClass($ruleClass)->permit()) {
                return false;
            }
        }
        return true;
    }
}