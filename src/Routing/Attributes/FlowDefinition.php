<?php
namespace Suphle\Routing\Attributes;

abstract class FlowDefinition {
    public function __construct(
        public readonly string $target, // The URI pattern (e.g. "users/{id}")
        public readonly string $source, // The key in the JSON/Markup response
        public readonly int $ttl = 600,
        public readonly int $maxHits = 1
    ) {}
}