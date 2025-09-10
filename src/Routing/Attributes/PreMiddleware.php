<?php

namespace Suphle\Routing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class PreMiddleware
{
    public function __construct(
        public readonly string $funnelClass
    ) {
        //
    }
} 