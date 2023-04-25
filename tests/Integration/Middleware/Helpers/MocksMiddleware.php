<?php

namespace Suphle\Tests\Integration\Middleware\Helpers;

use Suphle\Contracts\Routing\Middleware;

trait MocksMiddleware
{
    // without this, we'll use getModules and then need to have test classes for each of these different configurations
    protected function getMiddlewareMock(
        string $className,
        int $numTimes,
        array $constructorArguments = []
    ): Middleware {

        return $this->positiveDouble($className, [

            "process" => $this->returnCallback(fn ($request, $requestHandler) => $requestHandler->handle($request))
        ], [

            "process" => [$numTimes, []]
        ], $constructorArguments);
    }

    protected function provideMiddleware(array $middlewareList): void
    {

        $this->getContainer()->whenTypeAny()->needsAny($middlewareList);
    }
}
