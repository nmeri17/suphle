<?php

namespace _modules_shell\_module_name\InstalledComponents\SuphleIdentity\Listeners;

use _modules_shell\_module_name\InstalledComponents\SuphleIdentity\MailBuilders\PasswordResetMailBuilder;

class PasswordResetMailReactor
{
    public function __construct(
        protected readonly PasswordResetMailBuilder $mailBuilder
    ) {}

    public function sendResetMail(array $payload): void
    {
        $this->mailBuilder->setPayload($payload)->sendMessage();
    }
}