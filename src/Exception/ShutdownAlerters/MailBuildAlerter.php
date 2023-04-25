<?php

namespace Suphle\Exception\ShutdownAlerters;

use Suphle\IO\Mailing\MailBuilder;

use Suphle\Contracts\IO\{EnvAccessor, MailClient};

class MailBuildAlerter extends MailBuilder
{
    public function __construct(
        protected readonly MailClient $mailClient,
        protected readonly EnvAccessor $envAccessor
    ) {

        //
    }

    public function sendMessage(): void
    {

        $this->mailClient->setDestination(
            $this->envAccessor->getField("MAIL_SHUTDOWN_RECIPIENT")
        )
        ->setSubject(
            $this->envAccessor->getField("MAIL_SHUTDOWN_SUBJECT")
        )
        ->setText($this->payload)->fireMail();
    }
}
