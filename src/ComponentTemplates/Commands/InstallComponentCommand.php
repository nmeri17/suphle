<?php
	namespace Suphle\ComponentTemplates\Commands;

	use Suphle\Console\BaseCliCommand;

	use Suphle\ComponentTemplates\ComponentEjector;

	use Symfony\Component\Console\Output\OutputInterface;

	use Symfony\Component\Console\Input\{InputInterface, InputArgument, InputOption};

	use Symfony\Component\Console\Command\Command;

	class InstallComponentCommand extends BaseCliCommand {

		public const OVERWRITE_OPTION = "overwrite";

		protected static $defaultDescription = "Extract templates registered for given module";

		public static function commandSignature ():string {

			return "templates:install";
		}

		protected function configure ():void {

			$this->setName(self::commandSignature());

			$this->addArgument(

				self::HYDRATOR_MODULE_OPTION, InputArgument::REQUIRED,

				"Module interface where templates are to be ejected"
			);

			$this->addOption(
				self::OVERWRITE_OPTION, "o",

				InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,

				"List of entries to override or empty to overwrite all",

				[] // default value. Means option wasn't passed. When value = [null], option is present but has no value aka "all"
			);
		}

		protected function execute (InputInterface $input, OutputInterface $output):int {

			$moduleInterface = $input->getArgument(self::HYDRATOR_MODULE_OPTION);

			$result = $this->getExecutionContainer($moduleInterface)

			->getClass(ComponentEjector::class)

			->depositFiles($this->getOverwriteOption($input));

			if ($result) {

				$output->writeln("Templates ejected successfully");

				return Command::SUCCESS;
			}

			return Command::FAILURE;
		}

		/**
		 * @see option definition for legend
		*/
		protected function getOverwriteOption (InputInterface $input):?array {

			$givenValue = $input->getOption(self::OVERWRITE_OPTION);

			if (is_array($givenValue) ) {

				if ( empty($givenValue)) return null;

				return array_filter($givenValue); // empty string or no value will populate this with nulls
			}

			return $givenValue; // will never get here since option is declared as an array
		}
	}
?>