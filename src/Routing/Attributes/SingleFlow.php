<?php

namespace Suphle\Routing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class SingleFlow extends FlowDefinition {
    public function __construct(
        string $target, string $source,
        public readonly SingleFlowOperation $operation = SingleFlowOperation::ALTERS_QUERY,
        int $ttl = 600, int $maxHits = 1
    ) { parent::__construct($target, $source, $ttl, $maxHits); }
}