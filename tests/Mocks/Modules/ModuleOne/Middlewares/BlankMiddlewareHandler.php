<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares;

use Suphle\Contracts\Presentation\BaseRenderer;

use Suphle\Middleware\{MiddlewareNexts, CollectibleMiddlewareHandler};

use Suphle\Request\PayloadStorage;

class BlankMiddlewareHandler extends CollectibleMiddlewareHandler
{
    public function process(PayloadStorage $request, ?MiddlewareNexts $requestHandler): BaseRenderer
    {

        return $requestHandler->handle($request);
    }
}
