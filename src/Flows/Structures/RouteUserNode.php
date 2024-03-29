<?php

namespace Suphle\Flows\Structures;

use Suphle\Contracts\Presentation\BaseRenderer;

use DateTime;
use DateInterval;

/**
 *  This is the smallest unit where the ultimate user related cached information is stored
*/
class RouteUserNode
{
    private $maxHitsHydrator;
    private $expiresAtHydrator;
    protected int $hits = 0;

    public function __construct(protected readonly BaseRenderer $renderer)
    {

        //
    }

    public function currentHits(): int
    {

        return $this->hits;
    }

    public function getMaxHits(string $userId, string $pattern): int
    {

        $callback = $this->maxHitsHydrator;

        if (is_null($callback)) {

            $callback = $this->defaultMaxHits();
        }

        return call_user_func_array($callback, [$userId, $pattern]);
    }

    protected function defaultMaxHits(): callable
    {

        return fn ($userId, $pattern) => 1;
    }

    /**
     * @param {callback} => Function (string $userId, string $pattern):int
    */
    public function setMaxHitsHydrator(callable $callback): self
    {

        $this->maxHitsHydrator = $callback;

        return $this;
    }

    public function incrementHits(): void
    {

        $this->hits++;
    }

    public function getExpiresAt(string $userId, string $pattern): DateTime
    {

        $callback = $this->expiresAtHydrator;

        if (is_null($callback)) {

            $callback = $this->defaultExpiresAt();
        }

        return call_user_func_array($callback, [$userId, $pattern]);
    }

    protected function defaultExpiresAt(): callable
    {

        return function ($userId, $pattern) {

            return (new DateTime())->add(new DateInterval("PT10M")); // store for 10 minutes
        };
    }

    /**
     * @param {callback} => Function (string $userId, string $pattern):DateTime
    */
    public function setExpiresAtHydrator(callable $callback): self
    {

        $this->expiresAtHydrator = $callback;

        return $this;
    }

    public function getRenderer(): BaseRenderer
    {

        return $this->renderer;
    }
}
