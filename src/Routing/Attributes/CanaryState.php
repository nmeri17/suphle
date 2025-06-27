<?php

namespace Suphle\Routing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class CanaryState
{
    public function __construct(
        public array $canaries // array of FQCN strings
    ) {}
} 