<?php

namespace Suphle\Routing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class CollectionFlow extends FlowDefinition
{
    public function __construct(
        string $target,
        string $source,
        public readonly CollectionFlowOperation $operation,
        public readonly ?string $columnName = null,
        public readonly ?array $rangeContext = null,
        public readonly ?string $serviceClass = null,
        public readonly ?string $serviceMethod = null,
        ?string $ttl = null,
        ?int $maxHits = null
    ) {
        parent::__construct($target, $source, $ttl, $maxHits);
    }
} 