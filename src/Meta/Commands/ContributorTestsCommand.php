<?php
	namespace Suphle\Meta\Commands;

	use Suphle\Meta\ProjectInitializer;

	use Suphle\Console\BaseCliCommand;

	use Symfony\Component\Console\{Output\OutputInterface, Command\Command};

	use Symfony\Component\Console\Input\{InputInterface, InputOption};

	use Throwable;

	class ContributorTestsCommand extends BaseCliCommand {

		final const TESTS_PATH_OPTION = "tests_path",

		PHPUNIT_ARGS_OPTION = "phpunit_arg";

		protected static $defaultDescription = "Install RR and run test suite";

		protected bool $withModuleOption = false;

		public static function commandSignature ():string {

			return "project:contribute_test";
		}

		protected function configure ():void {

			parent::configure();

			$this->addOption(
				self::TESTS_PATH_OPTION, "t", InputOption::VALUE_REQUIRED,

				"Absolute path. Defaults to root tests folder"
			);

			$this->addOption(
				self::PHPUNIT_ARGS_OPTION, "p",

				InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,

				"Arguments to forward to the phpunit runner", []
			);
		}

		public function execute (InputInterface $input, OutputInterface $output):int {

			try {

				$this->getExecutionContainer(null)

				->getClass(ProjectInitializer::class)

				->sendRootPath($this->executionPath)
				
				->contributorOperations(

					$input->getOption(self::TESTS_PATH_OPTION),

					$input->getOption(self::PHPUNIT_ARGS_OPTION)
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