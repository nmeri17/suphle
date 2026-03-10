<?php

namespace Suphle\WebSockets\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class WsRoute
{
    public function __construct(
        public readonly string $path
    ) {}
}
