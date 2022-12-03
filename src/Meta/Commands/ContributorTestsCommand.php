<?php
	namespace Suphle\Meta\Commands;

	use Suphle\Server\ProjectInitializer;

	use Suphle\Console\BaseCliCommand;

	use Symfony\Component\Console\{Output\OutputInterface, Command\Command};

	use Symfony\Component\Console\Input\{InputInterface, InputArgument};

	use Throwable;

	class ContributorTestsCommand extends BaseCliCommand {

		final const TESTS_PATH_ARGUMENT = "tests_path";

		protected static $defaultDescription = "Install RR and run test suite";

		protected bool $withModuleOption = false;

		public static function commandSignature ():string {

			return "project:contribute_test";
		}

		protected function configure ():void {

			parent::configure();

			$this->addArgument(
				self::TESTS_PATH_ARGUMENT, InputArgument::REQUIRED, "Absolute path"
			);
		}

		public function execute (InputInterface $input, OutputInterface $output):int {

			try {

				$this->getExecutionContainer()->getClass(ProjectInitializer::class)
				
				->contributorOperations(

					$input->getArgument(self::TESTS_PATH_ARGUMENT)
				);

				return Command::SUCCESS;
			}
			catch (Throwable $exception) {

				echo($exception);

				$output->writeln($exception);

				return Command::FAILURE;
			}
		}
	}
?>