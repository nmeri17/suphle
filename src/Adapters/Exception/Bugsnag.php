<?php

namespace Suphle\Adapters\Exception;

use Suphle\Contracts\Exception\AlertAdapter;

use Bugsnag\Client as BugsnagClient;

use Throwable;

class Bugsnag implements AlertAdapter
{
    protected $client;

    public function __construct()
    {

        $this->client = BugsnagClient::make(); // expects to read API credentials from env
    }

    public function broadcastException(Throwable $exception, $activePayload): void
    {

        $this->client->notifyException($exception, function ($report) use ($activePayload) {

            // $report->setSeverity('info');

            $report->setMetaData($activePayload);
        });
    }
}
