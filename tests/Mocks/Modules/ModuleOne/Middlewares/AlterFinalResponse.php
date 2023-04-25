<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares;

use Suphle\Contracts\{Presentation\BaseRenderer, Routing\Middleware};

use Suphle\Middleware\MiddlewareNexts;

use Suphle\Request\PayloadStorage;

class AlterFinalResponse implements Middleware
{
    public function process(PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler): BaseRenderer
    {

        $originalRenderer = $requestHandler->handle($payloadStorage);

        $originalRenderer->setRawResponse(array_merge(
            $originalRenderer->getRawResponse(),
            ["foo" => "baz"]
        ));

        return $originalRenderer;
    }
}
