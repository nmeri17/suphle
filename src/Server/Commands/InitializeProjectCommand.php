<?php
	namespace Suphle\Server\Commands;

	use Suphle\Server\ProjectInitializer;

	use Suphle\Console\BaseCliCommand;

	use Symfony\Component\Console\{Output\OutputInterface, Command\Command};

	use Symfony\Component\Console\Input\{InputInterface, InputArgument};

	use Throwable;

	class InitializeProjectCommand extends BaseCliCommand {

		protected static $defaultDescription = "Create a module and start RR server";

		protected bool $withModuleOption = false;

		protected function configure ():void {

			parent::configure();

			$this->addArgument(
				CloneModuleCommand::MODULE_NAME_ARGUMENT, InputArgument::REQUIRED, "Module to create"
			);

			$this->addOption(
				CloneModuleCommand::DESCRIPTOR_OPTION, "e",

				InputOption::VALUE_REQUIRED, "Descriptor presence will enable templates installation"
			);
		}

		public static function commandSignature ():string {

			return "project:create_new";
		}

		public function execute (InputInterface $input, OutputInterface $output):int {

			try {

				$this->getExecutionContainer()->getClass(ProjectInitializer::class)
				->allInitOperations(

					$input->getArgument(CloneModuleCommand::MODULE_NAME_ARGUMENT),

					$input->getOption(CloneModuleCommand::DESCRIPTOR_OPTION),

					$output
				);

				return Command::SUCCESS;
			}
			catch (Throwable $exception) {

				$output->writeln($exception);

				return Command::FAILURE;
			}
		}
	}
?>