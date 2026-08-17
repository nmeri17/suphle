<?php
namespace _modules_shell\_module_name\InstalledComponents\SuphleIdentity\Events;

use Suphle\Events\EventManager;

use _modules_shell\_module_name\InstalledComponents\SuphleIdentity\Services\{RegisterService, PasswordResetService};

use _modules_shell\_module_name\InstalledComponents\SuphleIdentity\Listeners\{VerificationMailReactor, PasswordResetMailReactor};

// register this if no prior listeners exist. otherwise, transfer its contents to registered listener and delete this
class AssignListeners extends EventManager
{
    public function registerListeners(): void
    {
        parent::registerListeners();

        $this->local(RegisterService::class, VerificationMailReactor::class)

        ->on(RegisterService::USER_REGISTERED, "sendVerificationMail");

        $this->local(PasswordResetService::class, PasswordResetMailReactor::class)
        ->on(PasswordResetService::RESET_REQUESTED, "sendResetMail");
    }
}