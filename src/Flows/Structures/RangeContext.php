<?php
namespace Suphle\Flows\Structures;

class RangeContext {

    public function __construct(
        public readonly string $parameterMax = "max",
        public readonly string $parameterMin = "min",
        public readonly bool $isDateMode = false
    ) {}
}
