<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares;

use Suphle\Contracts\{Presentation\BaseRenderer, Routing\Middleware};

use Suphle\Middleware\{MiddlewareNexts, BaseMiddleware};

use Suphle\Request\PayloadStorage;

use Suphle\Response\Format\Json;

class IgnoresLowerMiddleware extends BaseMiddleware
{
    public function process(PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler): BaseRenderer
    {

        return (new Json([]))->setRawResponse(["foo" => "bar"]);
    }
}
