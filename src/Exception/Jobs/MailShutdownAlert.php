<?php

namespace Suphle\Exception\Jobs;

use Suphle\Contracts\Exception\FatalShutdownAlert;

use Suphle\Exception\ShutdownAlerters\MailBuildAlerter;

class MailShutdownAlert implements FatalShutdownAlert
{
    protected string $errorDetails;

    public function __construct(protected readonly MailBuildAlerter $mailAlerter)
    {

        //
    }

    public function setErrorAsJson(string $errorDetails): void
    {

        $this->errorDetails = $errorDetails;
    }

    public function handle(): void
    {

        $this->mailAlerter->setPayload($this->errorDetails)

        ->sendMessage();
    }
}
