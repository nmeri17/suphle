<?php
	namespace Suphle\Modules\Commands;

	use Suphle\Modules\ProjectInitializer;

	use Suphle\Console\BaseCliCommand;

	use Symfony\Component\Console\{Output\OutputInterface, Command\Command};

	use Symfony\Component\Console\Input\{InputInterface, InputArgument};

	class InitializeProjectCommand extends BaseCliCommand {

		final public const MODULE_NAME_ARGUMENT = "new_module_name";

		protected static $defaultDescription = "Create a module and start RR server";

		protected bool $withModuleOption = false;

		protected function configure ():void {

			parent::configure();

			$this->addArgument(
				
				self::MODULE_NAME_ARGUMENT, InputArgument::REQUIRED, "First module to create"
			);
		}

		public static function commandSignature ():string {

			return "project:create_new"; // contribute_test
		}

		public function execute (InputInterface $input, OutputInterface $output):int {

			try {

				$this->getExecutionContainer()->getClass(ProjectInitializer::class)
				->allInitOperations(

					$input->getArgument(self::MODULE_NAME_ARGUMENT)
				);

				$output->writeln("Operation completed successfully");

				return Command::SUCCESS; // Command::SUCCESS/FAILURE/INVALID
			}
			catch (Throwable $exception) {

				$output->writeln($exception);

				return Command::FAILURE;
			}
		}
	}
?>