<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Commands;

	use Suphle\Console\BaseCliCommand;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\BCounter;

	use Symfony\Component\Console\{Output\OutputInterface, Command\Command};

	use Symfony\Component\Console\Input\{InputInterface, InputOption, InputArgument};

	class AltersConcreteCommand extends BaseCliCommand {

		protected function configure ():void {

			parent::configure();

			$this->addArgument(
				"new_value", InputArgument::REQUIRED, "Value to update concrete to"
			);
		}

		protected function commandSignature ():string {

			return "test:alters_concrete";
		}

		public function execute (InputInterface $input, OutputInterface $output):int {

			$this->moduleToRun($input)->getContainer()->getClass(BCounter::class)

			->setCount($input->getArgument("new_value"));

			$output->writeln("Operation completed successfully");

			return Command::SUCCESS; // Command::SUCCESS/FAILURE/INVALID
		}
	}
?>