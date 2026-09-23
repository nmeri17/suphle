<?php
namespace Suphle\Contracts\Routing;

use Suphle\Contracts\Presentation\BaseRenderer;

interface MiddlewareRegistry {

	public function runStack(array $preMiddleware, array $middleware):BaseRenderer;
}