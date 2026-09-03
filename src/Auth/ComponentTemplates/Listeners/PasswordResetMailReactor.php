<?php
namespace _modules_shell\_module_name\_event_listeners;

use Suphle\Events\Attributes\{HasHandlers, EventListener};
use _modules_shell\_module_name\InstalledComponents\SuphleIdentity\Services\PasswordResetService;
use _modules_shell\_module_name\InstalledComponents\SuphleIdentity\MailBuilders\PasswordResetMailBuilder;

#[HasHandlers(PasswordResetService::class)]
class PasswordResetMailReactor
{
    public function __construct(
        protected readonly PasswordResetMailBuilder $mailBuilder
    ) {}

    #[EventListener(PasswordResetService::RESET_REQUESTED)]
    public function sendResetMail(array $payload): void
    {
        $this->mailBuilder->setPayload($payload)->sendMessage();
    }
}