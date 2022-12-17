<?php
	namespace Suphle\Contracts\Server;

	interface OnStartup {

		public function runOperations (string $executionPath, array $commandOptions):void;
	}
?>