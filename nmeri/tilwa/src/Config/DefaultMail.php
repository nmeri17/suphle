<?php
	namespace Tilwa\Config;

	use Tilwa\Contracts\{Config\Mail as MailContract, IO\EnvAccessor};

	class DefaultMail implements MailContract {

		protected $envAccessor;

		public function __construct (EnvAccessor $envAccessor) {

			$this->envAccessor = $envAccessor;
		}

		public function smtpCredentials ():array {

			return [
				"smtp" => $this->envAccessor->getField("MAIL_SMTP"),

				"smtp_port" => $this->envAccessor->getField("MAIL_PORT")
			];
		}
	}
?>