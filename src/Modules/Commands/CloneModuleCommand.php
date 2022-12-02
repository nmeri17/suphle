<?php
	namespace Suphle\Modules\Commands;

	use Suphle\Console\BaseCliCommand;

	use Suphle\Modules\ModuleCloneService;

	use Symfony\Component\Console\{Output\OutputInterface, Command\Command};

	use Symfony\Component\Console\Input\{InputInterface, InputArgument, InputOption};

	use Throwable;

	class CloneModuleCommand extends BaseCliCommand {

		final public const SOURCE_ARGUMENT = "template_source",

		DESTINATION_OPTION = "destination_path",

		MODULE_NAME_ARGUMENT = "new_module_name",

		RELATIVE_SOURCE_OPTION = "is_relative_source",

		DESCRIPTOR_OPTION = "module_descriptor";

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

			$this->addOption(
				self::DESCRIPTOR_OPTION, "e",

				InputOption::VALUE_REQUIRED, "Descriptor presence will enable templates installation"
			);
		}

		public static function commandSignature ():string {

			return "modules:create";
		}

		protected function execute (InputInterface $input, OutputInterface $output):int {

			$moduleName = $input->getArgument(self::MODULE_NAME_ARGUMENT);

			try {

				$clonerService = $this->getClonerService($input);

				$templatesStatus = Command::FAILURE;

				if ($clonerService->createModuleFolder($moduleName))

					$templatesStatus = $clonerService->installModuleTemplates(

						$moduleName, $input, $output
					);

				if ($templatesStatus == Command::SUCCESS) {

					$output->writeln("Module $moduleName created successfully");

					return $templatesStatus;
				}
				
				return $templatesStatus;
			}
			catch (Throwable $exception) {

				$exceptionOutput = "Failed to create module $moduleName:\n". $exception;

				echo( $exceptionOutput); // leaving this in since writeln doesn't work in tests
				
				$output->writeln($exceptionOutput);

				return Command::INVALID;
			}
		}

		protected function getClonerService (InputInterface $input):ModuleCloneService {

			return $this->getExecutionContainer(

				$input->getOption(self::HYDRATOR_MODULE_OPTION)
			)->getClass(ModuleCloneService::class)

			->setCommandDetails(

				$input, $this->executionPath, $this->moduleList
			);
		}
	}
?>