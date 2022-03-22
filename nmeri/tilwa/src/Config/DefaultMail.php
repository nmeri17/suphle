<?php
	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\Mail as MailContract;

	class DefaultMail implements MailContract {

		public function smtpCredentials ():array {

			return [
				"smtp" => getenv('MAIL_SMTP'),

				"smtp_port" => getenv('MAIL_PORT')
			];
		}
	}
?>