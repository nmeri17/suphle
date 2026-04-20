<?php
namespace Suphle\Routing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class PreMiddleware {
    public function __construct(
        public readonly string $handlerClass,

        public readonly array $args = []
    ) {}
}