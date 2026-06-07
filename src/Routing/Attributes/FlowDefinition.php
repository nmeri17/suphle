<?php
namespace Suphle\Routing\Attributes;

use DateTime, DateInterval;

abstract class FlowDefinition {
    public function __construct(
        public readonly string $target, // The URI pattern (e.g. "users/{id}")
        public readonly string $source, // The key in the JSON/Markup response
        public readonly int $maxHits = 1,
        public ?DateTime $ttl = null
    ) {
        $this->ensureHasTtl();
    }

    protected function ensureHasTtl ():void {

        if (is_null($this->ttl))

            $this->ttl = (new DateTime())->add(new DateInterval("PT10M"));
    }
}