<?php

namespace Suphle\Middleware;

use Suphle\Contracts\Routing\Middleware;

use Suphle\Routing\Structures\ReceivesMetaFunnel;

abstract class CollectibleMiddlewareHandler implements Middleware
{
    use ReceivesMetaFunnel;
}
