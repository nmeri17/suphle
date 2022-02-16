<?php
	namespace Tilwa\Bridge\Laravel\Cli;

	use Tilwa\Contracts\Bridge\{LaravelContainer, LaravelArtisan};

	use Symfony\Component\Console\{Command\Command, Output\OutputInterface};

	use Symfony\Component\Console\Input\{InputInterface, InputOption, InputArgument};

	/**
	 * All we want is for our ormBridge to run, hydrate and link our connection to the instance artisan works with
	*/
	class ArtisanCli extends Command {

		protected static $defaultName = "bridge:laravel";

		protected static $defaultDescription = "Interface with artisan commands";

		protected function configure ():void {

			$this

			->addArgument("to_forward", InputArgument::REQUIRED, "Commands to forward to artisan")

			->addOption(
				"module", "m", InputOption::VALUE_REQUIRED, "Executable version of the module to migrate"
			);
		}

		protected function execute (InputInterface $input, OutputInterface $output):int {

			$moduleName = $input->getOption("module");

			$executableModule = new $moduleName;

			$executableModule->boot();

			$exitCode = $executableModule->getContainer()->getClass(LaravelArtisan::class)

			->call($input->getArgument("to_forward"));

			$output->writeln("Operation completed successfully");

			return $exitCode; // Command::SUCCESS/FAILURE/INVALID
		}
	}
?>