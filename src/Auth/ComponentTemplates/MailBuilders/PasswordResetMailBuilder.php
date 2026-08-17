<?php
namespace _modules_shell\_module_name\InstalledComponents\SuphleIdentity\MailBuilders;

use Suphle\IO\Mailing\MailBuilder;
use Suphle\Contracts\IO\MailClient;
use Suphle\Routing\NamedRouteReader;
use _modules_shell\_module_name\Coordinators\PasswordResetCoordinator;

class PasswordResetMailBuilder extends MailBuilder
{
    public function __construct(
        protected readonly MailClient $mailClient,
        protected readonly NamedRouteReader $routeReader
    ) {}

    public function sendMessage(): void
    {
        $resetLink = $this->routeReader->expandRoute(
            PasswordResetCoordinator::class, "showResetForm",
            ["token" => $this->payload["token"]]
        );

        $this->mailClient
            ->setDestination($this->payload["email"])
            ->setSubject("Reset Your Password")
            ->setText("Click the link below to reset your password:\n\n" . $resetLink)
            ->fireMail();
    }
}