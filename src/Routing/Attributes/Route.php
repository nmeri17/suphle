<?php

namespace Suphle\Routing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    public function __construct(
        public readonly string $path,
        public readonly HttpMethod $method = HttpMethod::GET,
        public readonly ?string $view_name = null
    ) {
        //
    }
} 