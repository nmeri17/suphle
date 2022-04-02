<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Commands;

	use Tilwa\Console\BaseCliCommand;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\BCounter;

	use Symfony\Component\Console\Output\OutputInterface;

	use Symfony\Component\Console\Input\{InputInterface, InputOption, InputArgument};

	class AltersConcreteCommand extends BaseCliCommand {

		protected static $defaultName = "test:alters_concrete";

		protected static $defaultDescription = "";

		protected function configure ():void {

			parent::configure();

			$this->addArgument(
				"new_value", InputArgument::REQUIRED, "Value to update concrete to"
			);
		}

		protected function execute (InputInterface $input, OutputInterface $output):int {

			$this->moduleToRun($input)->getContainer()->getClass(BCounter::class)

			->setCount($input->getArgument("new_value"));

			$output->writeln("Operation completed successfully");

			return Command::SUCCESS; // Command::SUCCESS/FAILURE/INVALID
		}
	}
?>