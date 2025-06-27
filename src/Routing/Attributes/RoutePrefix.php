<?php

namespace Suphle\Routing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class RoutePrefix
{
    public function __construct(
        public readonly string $prefix
    ) {
        //
    }
} 