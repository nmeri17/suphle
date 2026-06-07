<?php
namespace Suphle\Routing\Attributes;

use Attribute, DateTime;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class SingleFlow extends FlowDefinition {
    public function __construct(
        string $target, string $source,
        public readonly SingleFlowOperation $operation = SingleFlowOperation::ALTERS_QUERY,
        int $maxHits = 1,
        ?DateTime $ttl = null
    ) { parent::__construct($target, $source, $maxHits, $ttl); }
}