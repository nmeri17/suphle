<?php
namespace Suphle\Events\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class HasHandlers {
    public function __construct(
        public readonly string $emitter,
        public readonly bool $isOuterModule = false
    ) {}
}