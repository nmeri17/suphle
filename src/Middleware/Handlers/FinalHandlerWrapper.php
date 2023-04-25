<?php

namespace Suphle\Middleware\Handlers;

use Suphle\Contracts\{Presentation\BaseRenderer, Routing\Middleware, Response\RendererManager};

use Suphle\Middleware\MiddlewareNexts;

use Suphle\Request\PayloadStorage;

class FinalHandlerWrapper implements Middleware
{
    public function __construct(protected readonly RendererManager $rendererManager)
    {

        //
    }

    public function process(PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler): BaseRenderer
    {

        $this->rendererManager->handleValidRequest($payloadStorage);

        $this->rendererManager->afterRender();

        return $this->rendererManager->responseRenderer();
    }
}
