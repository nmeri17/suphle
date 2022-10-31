<?php
	namespace Suphle\IO\Mailing;

	use Suphle\Hydration\BaseInterfaceLoader;

	use Suphle\Adapters\Mailers\SymfonyMailer;

	use Suphle\Contracts\IO\EnvAccessor;

	use Symfony\Component\Mailer\{Transport, Mailer};

	use Symfony\Component\Mime\Email;

	class MailClientLoader extends BaseInterfaceLoader {

		public function __construct(private readonly EnvAccessor $envAccessor)
  {
  }

		public function bindArguments ():array {

			$connection = $this->envAccessor->getField("MAIL_SMTP");

			return [

				"bodyWriter" => new Email,

				"sender" => new Mailer(Transport::fromDsn($connection))
			];
		}

		public function concreteName ():string {

			return SymfonyMailer::class;
		}
	}
?>