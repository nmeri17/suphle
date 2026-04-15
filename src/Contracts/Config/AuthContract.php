<?php

namespace Suphle\Contracts\Config;

use Suphle\Contracts\Auth\LoginFlowMediator;

interface AuthContract extends ConfigMarker
{
    /**
     * @return destination when user hits SessionStorage protected route
    */
    public function markupRedirect(): string;

    // [<Model> => <ModelAuthorities>]
    public function getModelObservers(): array;
}
