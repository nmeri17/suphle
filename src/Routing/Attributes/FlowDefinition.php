<?php

namespace Suphle\Routing\Attributes;

abstract class FlowDefinition
{
    public function __construct(
        public readonly string $target,
        public readonly string $source,
        public readonly ?string $ttl = null,
        public readonly ?int $maxHits = null
    ) {}
} 