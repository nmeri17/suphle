<?php

namespace Suphle\Adapters\Queues;

use Suphle\Contracts\Queues\Adapter;

use Suphle\Hydration\Container;

abstract class BaseQueueAdapter implements Adapter
{
    protected string $activeQueueName;

    protected $client; // set from `configureNative`,

    public function __construct(protected readonly Container $container)
    {

        //
    }

    public function setActiveQueue(string $queueName): void
    {

        $this->activeQueueName = $queueName;
    }

    public function getNativeClient()
    {

        return $this->client;
    }

    protected function hydrateTask(string $taskName, array $argumentList): object
    {

        return $this->container->whenType($taskName)

        ->needsArguments($argumentList)->getClass($taskName);
    }
}
