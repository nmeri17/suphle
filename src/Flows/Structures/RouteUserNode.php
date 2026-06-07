<?php
namespace Suphle\Flows\Structures;

use Suphle\Contracts\Presentation\BaseRenderer;

use Suphle\Routing\Structures\RouteInfo;

use DateTime, DateInterval;

/**
 *  This is the smallest unit where the ultimate user related cached information is stored
*/
class RouteUserNode
{
    protected DateTime $expiresAt;

    protected int $hits = 0, $maxHits = 0;

    public function __construct(
        public readonly BaseRenderer $renderer,
        public readonly RouteInfo $routeDetails
    ) { }

    public function hasExceededMaxHits():bool
    {
        return $this->hits >= $this->maxHits-1;
    }

    public function incrementHits(): void
    {
        $this->hits++;
    }

    public function setMetaDetails(DateTime $time, int $maxHits):void
    {
        $this->expiresAt = $time;

        $this->maxHits = $maxHits;
    }
    public function notExpired (DateTime $time):bool {

        return $this->expiresAt >= $time;
    }
}
