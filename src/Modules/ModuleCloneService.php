<?php
	namespace Suphle\Modules;

	use Suphle\Contracts\{ConsoleClient, Modules\DescriptorInterface};

	use Suphle\Hydration\Container;

	use Suphle\Modules\Commands\CloneModuleCommand;

	use Suphle\File\{FolderCloner, FileSystemReader};

	use Suphle\ComponentTemplates\Commands\InstallComponentCommand;

	use Symfony\Component\Console\{Output\OutputInterface, Command\Command};

	use Symfony\Component\Console\Input\{InputInterface, ArrayInput};

	class ModuleCloneService {

		protected InputInterface $input;

		protected string $executionPath;

		protected array $moduleList;

		public function __construct (

			private readonly FileSystemReader $fileSystemReader,

			private readonly ConsoleClient $consoleClient,

			private readonly FolderCloner $folderCloner
		) {

			//
		}

		public function setCommandDetails (InputInterface $input, string $executionPath, array $moduleList):self {

			$this->input = $input;

			$this->executionPath = $executionPath;

			$this->moduleList = $moduleList;

			return $this;
		}

		public function createModuleFolder (string $moduleName):bool {

			return $this->folderCloner->setEntryReplacements(

				$this->getFileReplacements($moduleName),

				$this->getFolderReplacements($moduleName),

				$this->getContentReplacements($moduleName)
			)
			->transferFolder(

				$this->getSource(), $this->getDestination($moduleName)
			);
		}

		public function installModuleTemplates (string $moduleName, InputInterface $input, OutputInterface $output):int {

			$descriptorName = $input->getOption(CloneModuleCommand::DESCRIPTOR_OPTION);

			if (empty($descriptorName)) return Command::SUCCESS;

			$descriptor = $this->bootNewlyCreatedContainer($descriptorName);

			$command = $this->consoleClient->findCommand( // using the client without binding cliRunner itself (and using that as we do in the tests) because commands shouldn't have access to that high level object and have no need for all the functionality it provides

				InstallComponentCommand::commandSignature()
			);

			$command->setModules([

				...$this->moduleList, $descriptor
			]);

			$commandInput = new ArrayInput([

				InstallComponentCommand::HYDRATOR_MODULE_OPTION => $descriptor->exportsImplements()
			]);

			return $command->run($commandInput, $output);
		}

		public function bootNewlyCreatedContainer (string $descriptorName):DescriptorInterface { // move to a service and stub that

			$descriptor = new $descriptorName(new Container);

			$descriptor->warmModuleContainer();

			$descriptor->prepareToRun();

			return $descriptor;
		}

		protected function getSource ():string {

			$sourceName = $this->input->getArgument(CloneModuleCommand::SOURCE_ARGUMENT);

			if (!$this->input->getOption(CloneModuleCommand::RELATIVE_SOURCE_OPTION))

				return $sourceName;

			return $this->fileSystemReader->noTrailingSlash(

				$this->executionPath
			) . DIRECTORY_SEPARATOR. $sourceName;
		}

		protected function getDestination (string $target):string {

			$destination = $this->fileSystemReader->noTrailingSlash(

				$this->input->getOption(CloneModuleCommand::DESTINATION_OPTION) ??

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