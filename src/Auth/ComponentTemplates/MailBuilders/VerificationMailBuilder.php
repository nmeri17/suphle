<?php
namespace _modules_shell\_module_name\InstalledComponents\SuphleIdentity\MailBuilders;

use Suphle\IO\Mailing\MailBuilder;
use Suphle\Contracts\IO\MailClient;
use Suphle\Routing\NamedRouteReader;
use _modules_shell\_module_name\Coordinators\BrowserAuthCoordinator;

class VerificationMailBuilder extends MailBuilder
{
    public function __construct(
        protected readonly MailClient $mailClient,
        protected readonly NamedRouteReader $routeReader
    ) {}
    
    public function sendMessage(): void
    {
        $verificationLink = $this->routeReader->expressUrl(BrowserAuthCoordinator::class, "verifyEmail", [
                
                "token" => $this->payload["verification_token"]
        ]);

        $this->mailClient
            ->setDestination($this->payload["email"])
            ->setSubject("Verify Your Email Address")
            ->setText(
                "Click the link below to verify your account:\n\n". $verificationLink
            )
            ->fireMail();
    }
}