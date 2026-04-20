<?php
namespace Suphle\Middleware;

use Suphle\Contracts\Routing\Middleware;

use Suphle\Hydration\Container;

abstract class BaseMiddleware implements Middleware
{
    protected array $args = [];

    public function __construct (protected readonly Container $container) {}

    public function setArgs(array $args): void
    {
        $this->args = $args;
    }
}