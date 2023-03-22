<?php
	namespace Suphle\Server\Commands;

	use Suphle\Contracts\Server\OnStartup;

	use Suphle\Hydration\Container;

	use Suphle\Server\HttpServerOperations;

	use Suphle\Console\BaseCliCommand;

	use Symfony\Component\Console\{Output\OutputInterface, Command\Command};

	use Symfony\Component\Console\Input\{InputInterface, InputArgument, InputOption};

	use Throwable;

	class HttpServerCommand extends BaseCliCommand {

		public const RR_CONFIG_OPTION = "rr_config_path",

		DISABLE_SANITIZATION_OPTION = "insane",

		CUSTOM_OPERATIONS = "operations_class",

		CUSTOM_CLASS_OPTIONS = "custom_operations_options",

		NO_REFACTOR_STATIC_OPTION = "no_static_refactor",

		IGNORE_STATIC_FAILURE_OPTION = "ignore_static_correct";

		protected static $defaultDescription = "Run build operations and start RR servers";

		protected bool $withModuleOption = false;

		protected function configure ():void {

			parent::configure();

			$this->addOption(
				self::RR_CONFIG_OPTION, "r",

				InputOption::VALUE_REQUIRED, "Path to custom RR config"
			);

			$this->addOption(
				self::DISABLE_SANITIZATION_OPTION, "i",

				InputOption::VALUE_NONE, "Prevent dependency sanitization"
			);

			$this->addOption(
				self::CUSTOM_OPERATIONS, "o",

				InputOption::VALUE_REQUIRED, "Class name of object implementing ". OnStartup::class
			);

			$this->addOption(
				self::CUSTOM_CLASS_OPTIONS, "c",

				InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, // the array reader requires the required option

				"Arguments to pass to the custom boot service class"
			);

			$this->addOption(
				self::NO_REFACTOR_STATIC_OPTION, "f",

				InputOption::VALUE_NONE, "Don't correct type violations found"
			);

			$this->addOption(
				self::IGNORE_STATIC_FAILURE_OPTION, "b",

				InputOption::VALUE_NONE, "Proceed with server build despite static errors"
			);
		}

		public static function commandSignature ():string {

			return "server:start";
		}

		public function execute (InputInterface $input, OutputInterface $output):int {

			try {

				$container = $this->getExecutionContainer(null);

				$serverOperations = $container

				->getClass(HttpServerOperations::class)

				->sendRootPath($this->executionPath);

				if (!$input->getOption(self::DISABLE_SANITIZATION_OPTION)) // absent

					$serverOperations->restoreSanity();

				$serverOperations->runStaticChecks(

					!$input->getOption(self::NO_REFACTOR_STATIC_OPTION), // means default value received by that method will be true i.e. it has to be present for refactor to be disabled

					$input->getOption(self::IGNORE_STATIC_FAILURE_OPTION)
				);

				$this->handleCustomOperations($input, $container);

				$serverOperations->startRRServer(

					$input->getOption(self::RR_CONFIG_OPTION)
				);

				return Command::SUCCESS;
			}
			catch (Throwable $exception) {

				$output->writeln($exception);

				echo $exception;

				return Command::FAILURE;
			}
		}

		protected function handleCustomOperations (InputInterface $input, Container $container):void {

			$operationName = $input->getOption(self::CUSTOM_OPERATIONS);

			if (!$operationName) return;

			$container->getClass($operationName)->runOperations(

				$this->executionPath,

				$input->getOption(self::CUSTOM_CLASS_OPTIONS)
			);
		}
	}
?>