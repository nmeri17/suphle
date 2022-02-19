<?php
	namespace Tilwa\Adapters\Console;

	use Tilwa\Contracts\ConsoleClient;

	use Tilwa\Console\BaseCliCommand;

	use Symfony\Component\Console\Application;

	class SymfonyCli extends Application implements ConsoleClient {

		public function addCommand (BaseCliCommand $command) {

			$this->add ($command);
		}
	}
?>