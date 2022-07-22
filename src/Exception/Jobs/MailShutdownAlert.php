<?php
	namespace Suphle\Exception\Jobs;

	use Suphle\Contracts\Exception\FatalShutdownAlert;

	use Suphle\Exception\ShutdownAlerters\MailBuildAlerter;

	class MailShutdownAlert implements FatalShutdownAlert {

		private $mailAlerter, $errorDetails;

		public function __construct (MailBuildAlerter $mailAlerter) {

			$this->mailAlerter = $mailAlerter;
		}

		public function setErrorAsJson (string $errorDetails):void {

			$this->errorDetails = $errorDetails;
		}

		public function handle ():void {

			$this->mailAlerter->setPayload($this->errorDetails)

			->sendMessage();
		}
	}
?>