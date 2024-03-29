<?php

namespace Suphle\Flows\Previous;

class SingleNode extends UnitNode
{
    final public const ALTERS_QUERY_SEGMENT = 1;

    public function __construct(string $nodeName)
    {

        $this->nodeName = $nodeName;
    }

    public function altersQuery(): self
    {

        $this->actions[self::ALTERS_QUERY_SEGMENT] = null;

        return $this;
    }
}
