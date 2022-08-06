<?php
	namespace Suphle\Modules\Commands;

	use Suphle\Console\BaseCliCommand;

	use Suphle\File\{FolderCloner, FileSystemReader};

	use Symfony\Component\Console\Output\OutputInterface;

	use Symfony\Component\Console\Input\{InputInterface, InputArgument};

	use Throwable;

	class CloneModuleCommand extends BaseCliCommand {

		protected $container, $withModuleOption = false;

		public const SOURCE_ARGUMENT = "template_folder",

		DESTINATION_ARGUMENT = "project_root",

		MODULE_NAME_ARGUMENT = "new_module_name";

		protected static $defaultDescription = "Copy and rename contents of a folder into a module";

		protected function configure ():void {

			parent::configure();

			$this->addArgument(
				self::SOURCE_ARGUMENT, InputArgument::REQUIRED, "Folder structure to be mirrored"
			);

			$this->addArgument(
				self::DESTINATION_ARGUMENT, InputArgument::OPTIONAL, "Destination folder to write to"
			);

			$this->addArgument(
				self::MODULE_NAME_ARGUMENT, InputArgument::REQUIRED, "Module to create"
			);
		}

		static public function commandSignature ():string {

			return "modules:create";
		}

		protected function execute (InputInterface $input, OutputInterface $output):int {

			$moduleName = $input->getArgument(self::MODULE_NAME_ARGUMENT);

			try {

				if ($this->getOperationResult($moduleName, $input)) {

					$output->writeln("Module $moduleName created successfully");

					return Command::SUCCESS;
				}
				return Command::FAILURE;
			}
			catch (Throwable $exception) {

				$output->writeln("Failed to create module $moduleName: \n". $exception);

				return Command::INVALID;
			}
		}

		protected function getOperationResult (string $moduleName, InputInterface $input):bool {

			$moduleInterface = $input->getOption(self::HYDRATOR_MODULE_OPTION);

			$this->container = $this->getExecutionContainer($moduleInterface);

			return $this->container->getClass(FolderCloner::class)

			->setEntryReplacements(

				$this->getFileReplacements($moduleName),

				$this->getFolderReplacements($moduleName),

				$this->getContentReplacements($moduleName)
			)
			->transferFolder(

				$input->getArgument(self::SOURCE_ARGUMENT),

				$this->getNewDestination($moduleName, $input)
			);
		}

		protected function getNewDestination (string $target, InputInterface $input):string {

			return $this->container->getClass(FileSystemReader::class)

			->noTrailingSlash(
				$input->getArgument(self::DESTINATION_ARGUMENT) ??

				$this->executionPath
			). DIRECTORY_SEPARATOR . $target;
		}

		protected function getFileReplacements (string $moduleName):array {

			return ["module_name" => $moduleName];
		}

		protected function getFolderReplacements (string $moduleName):array {

			return ["module_name" => $moduleName];
		}

		protected function getContentReplacements (string $moduleName):array {

			return ["module_name" => $moduleName];
		}
	}
?>