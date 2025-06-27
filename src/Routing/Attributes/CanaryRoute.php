<?php

namespace Suphle\Routing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class CanaryRoute
{
    public function __construct(
        public readonly array $evaluators,
        public readonly string $fallback
    ) {
        //
    }
} 