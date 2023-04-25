<?php

namespace Suphle\Queues;

use Suphle\Contracts\Queues\Adapter;

class AdapterManager
{
    public function __construct(protected readonly Adapter $activeAdapter)
    {

        //
    }

    public function addTask(string $taskClass, array $payload = []): void
    {

        $this->activeAdapter->pushAction($taskClass, $payload);
    }

}
