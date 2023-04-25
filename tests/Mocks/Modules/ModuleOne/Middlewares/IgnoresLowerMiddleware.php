<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares;

use Suphle\Contracts\{Presentation\BaseRenderer, Routing\Middleware};

use Suphle\Middleware\MiddlewareNexts;

use Suphle\Request\PayloadStorage;

use Suphle\Response\Format\Json;

class IgnoresLowerMiddleware implements Middleware
{
    public function process(PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler): BaseRenderer
    {

        return (new Json(""))->setRawResponse(["foo" => "bar"]);
    }
}
