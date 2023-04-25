<?php

namespace Suphle\IO\Mailing;

use Suphle\Hydration\BaseInterfaceLoader;

use Suphle\Adapters\Mailers\SymfonyMailer;

use Suphle\Contracts\IO\EnvAccessor;

use Symfony\Component\Mailer\{Transport, Mailer};

use Symfony\Component\Mime\{Email, Message};

class MailClientLoader extends BaseInterfaceLoader
{
    public function __construct(protected readonly EnvAccessor $envAccessor)
    {

        //
    }

    public function bindArguments(): array
    {

        $connection = $this->envAccessor->getField("MAIL_SMTP");

        return [

            Message::class => new Email(),

            Mailer::class => new Mailer(Transport::fromDsn($connection))
        ];
    }

    public function concreteName(): string
    {

        return SymfonyMailer::class;
    }
}
