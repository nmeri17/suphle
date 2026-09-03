<?php
namespace Suphle\Flows\Structures;

class AccessContext
{
    public function __construct (
        public readonly string $path,
        public readonly RouteUserNode $unitPayload,
        public readonly RouteUmbrella $umbrella,
        public readonly string $userId
    ) {}
}
