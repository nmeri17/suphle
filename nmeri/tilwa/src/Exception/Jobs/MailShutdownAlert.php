<?php
	namespace Tilwa\Exception\Jobs;

	use Tilwa\Contracts\Exception\FatalShutdownAlert;

	use Tilwa\Exception\ShutdownAlerters\MailBuildAlerter;

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