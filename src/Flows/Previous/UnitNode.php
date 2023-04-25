<?php

namespace Suphle\Flows\Previous;

/**
 * Represents a meta map of actions to take on a previous response node when it's hydrated
*/
abstract class UnitNode
{
    protected $nodeName; // the key on the previous response body this node is attached to
    protected $actions = []; // on CollectionNodes, this is the list of actions to take, while on SingleNodes, this is the list of attributes applied

    protected array $config = [];

    final public const TTL = 1;
    final public const MAX_HITS = 2;

    public function getActions(): array
    {

        return $this->actions;
    }

    public function getNodeName(): string
    {

        return $this->nodeName;
    }

    /**
     * @param {callback} => Function (string $userId, string $pattern):DateTime
    */
    public function setTTL(callable $callback): self
    {

        $this->config[self::TTL] = $callback;

        return $this;
    }

    /**
     * Expire cache contents after this value elapses
     *
     * @param {callback} => Function (string $userId, string $pattern):int
    */
    public function setMaxHits(callable $callback): self
    {

        $this->config[self::MAX_HITS] = $callback;

        return $this;
    }

    public function getConfig(): array
    {

        return $this->config;
    }
}
