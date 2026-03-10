<?php

namespace Suphle\Contracts\Routing;

use Suphle\Routing\Structures\RouteInfo;

interface RouteDispatcher
{
    /**
     * Executes the handler associated with the given route
     *
     * @return mixed The response from the coordinator
     */
    public function dispatchRoute(RouteInfo $route): mixed;
}
