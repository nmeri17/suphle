<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares;

use Suphle\Contracts\{Presentation\BaseRenderer, Routing\Middleware};

use Suphle\Middleware\{MiddlewareNexts, BaseMiddleware};

use Suphle\Request\PayloadStorage;

class AlterFinalResponse extends BaseMiddleware
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
