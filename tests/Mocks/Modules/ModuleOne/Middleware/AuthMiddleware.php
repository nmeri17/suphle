<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Middleware;

use Suphle\Contracts\{Presentation\BaseRenderer, Routing\Middleware};
use Suphle\Middleware\MiddlewareNexts;
use Suphle\Request\PayloadStorage;
use Suphle\Response\Format\Json;

class AuthMiddleware implements Middleware
{
    public function process(
        PayloadStorage $request, 
        ?MiddlewareNexts $requestHandler
    ): BaseRenderer {
        // Simulate authentication check
        if (!$this->isAuthenticated($request)) {
            // Return JSON error response for API routes
            return new Json(['error' => 'Authentication required'], 401);
        }

        // Continue to the next middleware/controller
        return $requestHandler->handle($request);
    }

    private function isAuthenticated(PayloadStorage $request): bool
    {
        // Mock authentication logic
        // In a real implementation, this would check authentication headers,
        // session data, or other authentication mechanisms
        return true; // For testing purposes, always return true
    }
} 