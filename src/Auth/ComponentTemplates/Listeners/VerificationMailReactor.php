<?php
namespace _modules_shell\_module_name\_event_listeners;

use Suphle\Events\Attributes\{HasHandlers, EventListener};
use _modules_shell\_module_name\InstalledComponents\SuphleIdentity\Services\RegisterService;
use _modules_shell\_module_name\InstalledComponents\SuphleIdentity\MailBuilders\VerificationMailBuilder;

#[HasHandlers(RegisterService::class)]
class VerificationMailReactor {

	public function __construct (protected readonly VerificationMailBuilder $mailBuilder) {}

	#[EventListener(RegisterService::USER_REGISTERED)]
	public function sendVerificationMail (array $payload):void {

		$this->mailBuilder->setPayload($payload)->sendMessage();
	}
}