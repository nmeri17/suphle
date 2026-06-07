<?php
namespace Suphle\Routing\Attributes;

use Suphle\Flows\Structures\{RangeContext, ServiceContext};

use Attribute, InvalidArgumentException, DateTime;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class CollectionFlow extends FlowDefinition {
    public function __construct(
        string $target, string $source,
        public readonly CollectionFlowOperation $operation = CollectionFlowOperation::PIPE_TO,
        public readonly string $columnName = "id", // The leaf key inside the collection
        public readonly ?RangeContext $rangeContext = null,

        public readonly ?ServiceContext $serviceContext = null,
        int $maxHits = 1,
        
        ?DateTime $ttl = null
    ) {
        $this->modeHasType(CollectionFlowOperation::RANGE, $this->rangeContext);

        $this->modeHasType(CollectionFlowOperation::SET_FROM_SERVICE, $this->serviceContext);

        parent::__construct($target, $source, $maxHits, $ttl);
    }

    protected function modeHasType (CollectionFlowOperation $mode, ?object $context):void {

        if ($this->operation === $mode && is_null($context))

            throw new InvalidArgumentException;
    }
}