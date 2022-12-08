<?php
	namespace Suphle\Server\Commands;

	use Suphle\Server\{DependencySanitizer, VendorBin};

	use Suphle\Hydration\Container;

	use Suphle\Console\BaseCliCommand;

	use Symfony\Component\Console\{Output\OutputInterface, Command\Command};

	use Symfony\Component\Console\Input\{InputInterface, InputArgument, InputOption};

	use Throwable;

	class HttpServerCommand extends BaseCliCommand {

		public const RR_CONFIG_OPTION = "rr_config_path",

		DISABLE_SANITIZATION_OPTION = "insane";

		protected static $defaultDescription = "Run build operations and start RR servers";

		protected bool $withModuleOption = false;

		protected Container $container;

		protected function configure ():void {

			parent::configure();

			$this->addOption(
				self::RR_CONFIG_OPTION, "c",

				InputOption::VALUE_REQUIRED, "Path to custom RR config"
			);

			$this->addOption(
				self::DISABLE_SANITIZATION_OPTION, "i",

				InputOption::VALUE_NONE, "Path to custom RR config"
			);
		}

		public static function commandSignature ():string {

			return "server:start";
		}

		public function execute (InputInterface $input, OutputInterface $output):int {

			$this->container = $this->getExecutionContainer();

			try {

				if (!is_null(

					$input->getOption(self::DISABLE_SANITIZATION_OPTION)
				))

					$this->restoreSanity();

				$this->startRRServer($input);

				return Command::SUCCESS;
			}
			catch (Throwable $exception) {

				$output->writeln($exception);

				return Command::FAILURE;
			}
		}

		protected function restoreSanity ():void {

			$sanitizer = $this->container->getClass(DependencySanitizer::class);

			$sanitizer->setExecutionPath($this->executionPath);

			$sanitizer->cleanseConsumers();
		}

		protected function startRRServer (InputInterface $input):void {

			$commandOptions = ["serve"];

			$configPath = $input->getOption(self::RR_CONFIG_OPTION);

			if (!is_null($configPath))

				$commandOptions = array_merge(

					$commandOptions, ["-c", $configPath]
				);

			$this->container->getClass(VendorBin::class)

			->setProcessArguments("rr", $commandOptions)

			->start();
		}
	}
?>