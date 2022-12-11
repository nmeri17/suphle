<?php
	namespace Suphle\Server\Commands;

	use Suphle\Server\HttpServerOperations;

	use Suphle\Console\BaseCliCommand;

	use Symfony\Component\Console\{Output\OutputInterface, Command\Command};

	use Symfony\Component\Console\Input\{InputInterface, InputArgument, InputOption};

	use Throwable;

	class HttpServerCommand extends BaseCliCommand {

		public const RR_CONFIG_OPTION = "rr_config_path",

		DISABLE_SANITIZATION_OPTION = "insane";

		protected static $defaultDescription = "Run build operations and start RR servers";

		protected bool $withModuleOption = false;

		protected function configure ():void {

			parent::configure();

			$this->addOption(
				self::RR_CONFIG_OPTION, "c",

				InputOption::VALUE_REQUIRED, "Path to custom RR config"
			);

			$this->addOption(
				self::DISABLE_SANITIZATION_OPTION, "i",

				InputOption::VALUE_NONE, "Prevent dependency sanitization"
			);
		}

		public static function commandSignature ():string {

			return "server:start";
		}

		public function execute (InputInterface $input, OutputInterface $output):int {

			try {

				$serverOperations = $this->getExecutionContainer(null)

				->getClass(HttpServerOperations::class)

				->sendRootPath($this->executionPath);

				if (!$input->getOption(self::DISABLE_SANITIZATION_OPTION)) // absent

					$serverOperations->restoreSanity();

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
	}
?>