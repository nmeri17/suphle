<?php
namespace Suphle\Middleware\Handlers;

use Suphle\Contracts\{Presentation\BaseRenderer, Response\RendererManager};

use Suphle\Middleware\{MiddlewareNexts, BaseMiddleware};

use Suphle\Request\PayloadStorage;

class FinalHandlerWrapper extends BaseMiddleware
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
