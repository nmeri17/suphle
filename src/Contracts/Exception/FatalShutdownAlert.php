<?php
	namespace Suphle\Contracts\Exception;

	use Suphle\Contracts\Queues\Task;

	interface FatalShutdownAlert extends Task { // the only entity permitted to consume Mailers

		public function setErrorAsJson (string $errorDetails):void;
	}
?>