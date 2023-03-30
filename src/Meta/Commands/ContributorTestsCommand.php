<?php
	namespace Suphle\Meta\Commands;

	use Suphle\Meta\ProjectInitializer;

	use Suphle\Console\BaseCliCommand;

	use Symfony\Component\Console\{Output\OutputInterface, Command\Command};

	use Symfony\Component\Console\Input\{InputInterface, InputOption};

	use Throwable;

	class ContributorTestsCommand extends BaseCliCommand {

		public const TESTS_PATH_OPTION = "tests_path",

		PHPUNIT_ARGS_OPTION = "phpunit_flags",

		PARALLEL_OPTION = "paratest_flags";

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

				InputOption::VALUE_REQUIRED,

				"Arguments to forward to the phpunit runner"
			);

			$this->addOption(
				self::PARALLEL_OPTION, "a",

				InputOption::VALUE_REQUIRED,

				"Use the paratest runner with given flags"
			);
		}

		public function execute (InputInterface $input, OutputInterface $output):int {

			$parallelOptions = $input->getOption(self::PARALLEL_OPTION);

			try {

				$this->getExecutionContainer(null)

				->getClass(ProjectInitializer::class)

				->sendRootPath($this->executionPath)
				
				->contributorOperations(

					$input->getOption(self::TESTS_PATH_OPTION),

					$this->getRunnerOptions($input, $parallelOptions),

					!is_null($parallelOptions)
				);

				return Command::SUCCESS;
			}
			catch (Throwable $exception) {

				echo($exception);

				$output->writeln($exception);

				return Command::FAILURE;
			}
		}

		protected function getRunnerOptions (InputInterface $input, ?string $parallelOptions):array {

			return array_filter([

				$input->getOption(self::PHPUNIT_ARGS_OPTION),

				$parallelOptions
			]);
		}
	}
?>