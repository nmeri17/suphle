<?php
	namespace Suphle\Adapters\Console;

	use Suphle\Contracts\ConsoleClient;

	use Suphle\Console\BaseCliCommand;

	use Symfony\Component\Console\Application;

	class SymfonyCli extends Application implements ConsoleClient {

		public function addCommand (BaseCliCommand $command) {

			$this->add ($command);
		}
	}
?>