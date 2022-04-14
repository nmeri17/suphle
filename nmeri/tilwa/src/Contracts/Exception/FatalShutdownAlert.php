<?php
	namespace Tilwa\Contracts\Exception;

	interface FatalShutdownAlert extends Task {

		public function setErrorAsJson (string $errorDetails):void;
	}
?>