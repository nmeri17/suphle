<?php
	namespace Suphle\Adapters\Mailers;

	use Suphle\Contracts\IO\MailClient;

	use Symfony\Component\{Mime\Message, Mailer\Mailer};

	class SymfonyMailer implements MailClient {

		public function __construct(
			protected readonly Message $bodyWriter,

			protected readonly Mailer $sender
		) {

			//
		}

		public function setDestination (string $destination):self {

			$this->bodyWriter->to($destination);

			return $this;
		}

		public function setSubject (string $subject):self {

			$this->bodyWriter->subject($subject);

			return $this;
		}

		public function setText (string $text):self {

			$this->bodyWriter->text($text);

			return $this;
		}

		public function setHtml (string $html):self {

			$this->bodyWriter->html($html);

			return $this;
		}

		public function fireMail ():void {

			$this->sender->send($this->bodyWriter);
		}

		public function getNativeClient () {

			return $this->bodyWriter;
		}
	}
?>