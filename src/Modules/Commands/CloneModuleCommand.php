<?php
	namespace Suphle\Modules\Commands;

	use Suphle\Console\BaseCliCommand;

	use Suphle\Modules\ModuleCloneService;

	use Suphle\ComponentTemplates\Commands\InstallComponentCommand;

	use Symfony\Component\Console\{Output\OutputInterface, Command\Command};

	use Symfony\Component\Console\Input\{InputInterface, InputArgument, InputOption};

	use Throwable;

	class CloneModuleCommand extends BaseCliCommand {

		final public const MODULE_NAME_ARGUMENT = "new_module_name",

		DESCRIPTOR_OPTION = "module_descriptor",

		DESTINATION_OPTION = "destination_path",

		SOURCE_OPTION = "template_source",

		ABSOLUTE_SOURCE_OPTION = "is_relative_source";

		protected static $defaultDescription = "Copy and rename contents of a folder into a module";

		protected function configure ():void {

			parent::configure();

			$this->addArgument(
				self::MODULE_NAME_ARGUMENT, InputArgument::REQUIRED, "Module to create"
			);

			$this->addOption(
				self::DESTINATION_OPTION, "d", InputOption::VALUE_REQUIRED,

				"Destination folder to write to"
			); // note argument ordering: options can't come before arguments

			$this->addOption(
				self::ABSOLUTE_SOURCE_OPTION, "i",

				InputOption::VALUE_NONE, "Set whether paths are relative or absolute"
			);

			$this->addOption(
				self::DESCRIPTOR_OPTION, "e",

				InputOption::VALUE_REQUIRED, "Descriptor presence will enable templates installation"
			);

			$this->addOption(
				self::SOURCE_OPTION, "t",

				InputOption::VALUE_OPTIONAL, "Folder structure to be mirrored",

				"ModuleTemplate"
			);

			$this->addOption(
				InstallComponentCommand::COMPONENT_ARGS_OPTION, "p",

				InputOption::VALUE_REQUIRED,

				"Arguments to pass to the components: foo=value uju=bar"
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

						$moduleName, $output,

						$input->getOption(self::DESCRIPTOR_OPTION),

						$input->getOption(InstallComponentCommand::COMPONENT_ARGS_OPTION)
					);

				if ($templatesStatus == Command::SUCCESS) {

					$output->writeln("$moduleName module created successfully");

					return $templatesStatus;
				}
				
				return $templatesStatus;
			}
			catch (Throwable $exception) {

				$exceptionOutput = "$moduleName module creation incomplete:\n". $exception;

				echo( $exceptionOutput); // leaving this in since writeln doesn't work in tests
				
				$output->writeln($exceptionOutput);

				return Command::INVALID;
			}
		}

		protected function getClonerService (InputInterface $input):ModuleCloneService {

			return $this->getExecutionContainer(

				$input->getOption(self::HYDRATOR_MODULE_OPTION)
			)->getClass(ModuleCloneService::class)

			->setConsoleDetails($this->executionPath, $this->moduleList)

			->setCommandDetails(

				$input->getOption(self::SOURCE_OPTION),

				$input->getOption(self::ABSOLUTE_SOURCE_OPTION), // absence = false i.e relative

				$input->getOption(self::DESTINATION_OPTION)
			);
		}
	}
?>