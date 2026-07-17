<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares;

use Suphle\Contracts\Presentation\BaseRenderer;

use Suphle\Middleware\{MiddlewareNexts, BaseMiddleware};

use Suphle\Request\PayloadStorage;

class BlankMiddleware2Handler extends BaseMiddleware
{
    public function process(PayloadStorage $request, ?MiddlewareNexts $requestHandler): BaseRenderer
    {

        return $requestHandler->handle($request);
    }
}
