<?php

namespace Suphle\Routing\Structures;

class CanaryInfo
{
    public function __construct(
        public readonly array $evaluators,
        public readonly string $fallback
    ) {
        //
    }
} 