<?php
namespace Suphle\Routing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class ClearMiddleware {
    public function __construct(public readonly string $middlewareClass) {}
}