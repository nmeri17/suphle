<?php
namespace _modules_shell\_module_name\SuphleIdentity\Listeners;

use _modules_shell\_module_name\SuphleIdentity\MailBuilders\VerificationMailBuilder;

class VerificationMailReactor {

	public function __construct (protected readonly VerificationMailBuilder $mailBuilder) {}

	public function sendVerificationMail (array $payload):void {

		$this->mailBuilder->setPayload($payload)->sendMessage();
	}
}