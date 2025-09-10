<?php

namespace Suphle\Routing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class SingleFlow extends FlowDefinition
{
    public function __construct(
        string $target,
        string $source,
        public readonly SingleFlowOperation $operation,
        ?string $ttl = null,
        ?int $maxHits = null
    ) {
        parent::__construct($target, $source, $ttl, $maxHits);
    }
} 