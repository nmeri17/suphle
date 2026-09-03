<?php
namespace Suphle\Events\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class EventListener {
    public function __construct(public readonly string $eventName) {}
}