<?php
	namespace Suphle\Modules\Commands;

	use Suphle\Console\BaseCliCommand;

	use Suphle\ComponentTemplates\ComponentEjector;

	use Symfony\Component\Console\Output\OutputInterface;

	use Symfony\Component\Console\Input\{InputInterface, InputArgument, InputOption};

	class InstallComponentCommand extends BaseCliCommand {

		public const OVERWRITE_OPTION = "overwrite";

		protected static $defaultDescription = "Extract templates registered for given module";

		static public function commandSignature ():string {

			return "templates:install";
		}

		protected function configure ():void {

			$this->setName(self::commandSignature());

			$this->addArgument(

				self::HYDRATOR_MODULE_OPTION, "m", InputArgument::REQUIRED,

				"Module interface where templates are to be ejected"
			);

			$this->addOption(
				self::OVERWRITE_OPTION, "o",

				InputOption::OPTIONAL | InputOption::IS_ARRAY,

				"List of entries to override or empty to overwrite all",

				[] // default value. Means option wasn't passed. When value null, option is present but has no value aka "all"
			);
		}

		protected function execute (InputInterface $input, OutputInterface $output):int {

			$moduleInterface = $input->getArgument(self::HYDRATOR_MODULE_OPTION);

			$result = $this->getExecutionContainer($moduleInterface)

			->getClass(ComponentEjector::class)

			->depositFiles($input->getArgument(self::OVERWRITE_OPTION));

			if ($result) {

				$output->writeln("Templates ejected successfully");

				return Command::SUCCESS;
			}

			return Command::FAILURE;
		}
	}
?>