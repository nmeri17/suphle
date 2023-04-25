<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares;

use Suphle\Contracts\{Presentation\BaseRenderer, Routing\Middleware};

use Suphle\Middleware\MiddlewareNexts;

use Suphle\Request\PayloadStorage;

class AltersPayloadStorage implements Middleware
{
    public function process(PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler): BaseRenderer
    {

        $payloadStorage->mergePayload($this->payloadUpdates());

        return $requestHandler->handle($payloadStorage);
    }

    public function payloadUpdates(): array
    {

        return ["foo" => "bar"];
    }
}
