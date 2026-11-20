<?php
namespace Suphle\Middleware\Handlers;

use Suphle\Middleware\{MiddlewareNexts, BaseMiddleware};

use Suphle\Request\PayloadStorage;

use Suphle\Response\Format\Json;

use Suphle\Contracts\Presentation\BaseRenderer;

/**
 * This seems like a far simpler alternative to route mirroring. But middleware only runs after routing. Moreover, this doesn't offer the flexibility of overriding mirrored routes
*/
class JsonNegotiator extends BaseMiddleware
{
    public function process(PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler): BaseRenderer
    {
        $renderer = $requestHandler->handle($payloadStorage);

        $isRelevant = $payloadStorage->acceptsJson() && !($renderer instanceof Json);

        if (!$isRelevant ) return $renderer;

        $newRenderer = new Json($renderer->getRawResponse());

        $newRenderer->setHeaders($renderer->getStatusCode(), $renderer->getHeaders());

        return $newRenderer;        
    }
}
