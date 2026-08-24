<?php
namespace Suphle\Contracts\Routing;

use Suphle\Contracts\Presentation\BaseRenderer;

interface MiddlewareRegistry {

	public function setRawHandlers (array $preMiddleware, array $middleware):self;

	public function runStack():BaseRenderer;
}