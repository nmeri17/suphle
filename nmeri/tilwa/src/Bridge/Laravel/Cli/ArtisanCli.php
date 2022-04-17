<?php
	namespace Tilwa\Bridge\Laravel\Cli;

	use Tilwa\Contracts\Bridge\{LaravelContainer, LaravelArtisan};

	use Tilwa\Console\BaseCliCommand;

	use Symfony\Component\Console\Output\OutputInterface;

	use Symfony\Component\Console\Input\{InputInterface, InputOption, InputArgument};

	/**
	 * All we want is for our ormBridge to run, hydrate and link our connection to the instance artisan works with
	*/
	class ArtisanCli extends BaseCliCommand {

		protected static $defaultName = "bridge:laravel";

		protected static $defaultDescription = "Interface with artisan commands";

		protected function configure ():void {

			parent::configure();

			$this->addArgument(
				"to_forward", InputArgument::REQUIRED, "Commands to forward to artisan"
			);
		}

		protected function execute (InputInterface $input, OutputInterface $output):int {

			$exitCode = $this->moduleToRun($input)->getContainer()->getClass(LaravelArtisan::class)

			->invokeCommand($input->getArgument("to_forward"));

			$output->writeln("Operation completed successfully");

			return $exitCode; // Command::SUCCESS/FAILURE/INVALID
		}
	}
?>