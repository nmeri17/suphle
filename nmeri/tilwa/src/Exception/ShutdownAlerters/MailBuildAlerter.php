<?php
	namespace Tilwa\Exception\ShutdownAlerters;

	use Tilwa\IO\Mailing\MailBuilder;

	use Tilwa\Contracts\IO\{EnvAccessor, MailClient};

	class MailBuildAlerter extends MailBuilder {

		private $mailClient, $envAccessor;

		public function __construct (MailClient $mailClient, EnvAccessor $envAccessor) {

			$this->mailClient = $mailClient;

			$this->envAccessor = $envAccessor;
		}

		public function sendMessage ():void {

			$this->mailClient->setDestination(

				$this->envAccessor->getField("MAIL_SHUTDOWN_RECIPIENT")
			)
			->setSubject(

				$this->envAccessor->getField("MAIL_SHUTDOWN_SUBJECT")
			)
			->setText($this->payload)->fireMail();
		}
	}
?>