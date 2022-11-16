<?php
	namespace Suphle\Modules\Commands;

	use Suphle\Console\BaseCliCommand;

	use Suphle\Hydration\Container;

	use Suphle\File\{FolderCloner, FileSystemReader};

	use Symfony\Component\Console\{Output\OutputInterface, Command\Command};

	use Symfony\Component\Console\Input\{InputInterface, InputArgument, InputOption};

	use Throwable;

	class CloneModuleCommand extends BaseCliCommand {

		protected Container $container;

		protected FileSystemReader $fileSystemReader;

		protected InputInterface $input;

		final public const SOURCE_ARGUMENT = "template_source",

		DESTINATION_OPTION = "destination_path",

		MODULE_NAME_ARGUMENT = "new_module_name",

		RELATIVE_SOURCE_OPTION = "is_relative_source";

		protected static $defaultDescription = "Copy and rename contents of a folder into a module";

		protected function configure ():void {

			parent::configure();

			$this->addArgument(
				self::SOURCE_ARGUMENT, InputArgument::REQUIRED, "Folder structure to be mirrored"
			);

			$this->addArgument(
				self::MODULE_NAME_ARGUMENT, InputArgument::REQUIRED, "Module to create"
			);

			$this->addOption(
				self::DESTINATION_OPTION, "d", InputOption::VALUE_REQUIRED,

				"Destination folder to write to"
			); // note argument ordering: options can't come before arguments

			$this->addOption(
				self::RELATIVE_SOURCE_OPTION, "r",

				InputOption::VALUE_NONE, "Set whether paths are relative or absolute"
			);
		}

		public static function commandSignature ():string {

			return "modules:create";
		}

		protected function execute (InputInterface $input, OutputInterface $output):int {

			$moduleName = $input->getArgument(self::MODULE_NAME_ARGUMENT);

			try {

				$this->input = $input;

				if ($this->getOperationResult($moduleName)) {

					$output->writeln("Module $moduleName created successfully");

					return Command::SUCCESS;
				}
				return Command::FAILURE;
			}
			catch (Throwable $exception) {

				var_dump("Failed to create module $moduleName: \n". $exception); // leaving this in since writeln doesn't work in tests
				
				$output->writeln("Failed to create module $moduleName: \n". $exception);

				return Command::INVALID;
			}
		}

		protected function getOperationResult (string $moduleName):bool {

			$this->setEssentials(
			
				$this->input->getOption(self::HYDRATOR_MODULE_OPTION)
			);

			return $this->container->getClass(FolderCloner::class)

			->setEntryReplacements(

				$this->getFileReplacements($moduleName),

				$this->getFolderReplacements($moduleName),

				$this->getContentReplacements($moduleName)
			)
			->transferFolder(

				$this->getSource(), $this->getDestination($moduleName)
			);
		}

		protected function setEssentials (?string $moduleInterface):void {

			$this->container = $this->getExecutionContainer($moduleInterface);

			$this->fileSystemReader = $this->container->getClass(FileSystemReader::class);
		}

		protected function getSource ():string {

			$sourceName = $this->input->getArgument(self::SOURCE_ARGUMENT);

			if (!$this->input->getOption(self::RELATIVE_SOURCE_OPTION))

				return $sourceName;

			return $this->fileSystemReader->noTrailingSlash($this->executionPath) . DIRECTORY_SEPARATOR. $sourceName;
		}

		protected function getDestination (string $target):string {

			$destination = $this->fileSystemReader->noTrailingSlash(

				$this->input->getOption(self::DESTINATION_OPTION) ??

				$this->executionPath
			). DIRECTORY_SEPARATOR . $target;

			return $this->fileSystemReader->pathFromLevels($destination, "", 1); // since we expect to modify even the root folder itself, not only the children
		}

		protected function getFileReplacements (string $moduleName):array {

			return ["_module_name" => $moduleName];
		}

		protected function getFolderReplacements (string $moduleName):array {

			return ["_module_name" => $moduleName];
		}

		protected function getContentReplacements (string $moduleName):array {

			return ["_module_name" => $moduleName];
		}
	}
?>