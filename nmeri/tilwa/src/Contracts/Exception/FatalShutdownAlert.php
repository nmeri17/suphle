<?php
	namespace Tilwa\Contracts\Exception;

	use Tilwa\Contracts\Queues\Task;

	interface FatalShutdownAlert extends Task { // the only entity permitted to consume Mailers

		public function setErrorAsJson (string $errorDetails):void;
	}
?>