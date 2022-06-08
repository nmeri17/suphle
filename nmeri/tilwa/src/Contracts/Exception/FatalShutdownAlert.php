<?php
	namespace Tilwa\Contracts\Exception;

	use Tilwa\Contracts\Queues\Task;

	interface FatalShutdownAlert extends Task {

		public function setErrorAsJson (string $errorDetails):void;
	}
?>