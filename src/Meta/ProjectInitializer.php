<?php
	namespace Suphle\Meta;

	use Suphle\Contracts\ConsoleClient;

	use Suphle\Modules\Commands\CloneModuleCommand;

	use Suphle\Server\VendorBin;

	class ProjectInitializer {

		public function __construct (

			private readonly ConsoleClient $consoleClient,

			private readonly VendorBin $vendorBin
		) {

			//
		}

		public function allInitOperations (string $moduleName, string $descriptorFqcn = null):int {

			$creationStatus = $this->createModule($moduleName, $descriptorFqcn);

			$this->vendorBin->setProcessArguments("rr", ["get-binary"])->run();

			$this->vendorBin->getServerLauncher("../../dev-rr.yaml")->start();

			return $creationStatus;
		}

		public function contributorOperations (string $testsPath):void {

			$this->vendorBin->setProcessArguments("rr", ["get-binary"])->run();

			$this->vendorBin->setProcessArguments("phpunit", [$testsPath])->run();
		}

		protected function createModule (string $moduleName, ?string $descriptorFqcn):int {

			$command = $this->consoleClient->findCommand(

				CloneModuleCommand::commandSignature()
			);

			$commandOptions = [

				CloneModuleCommand::MODULE_NAME_ARGUMENT => $moduleName
			];

			if (!is_null($descriptorFqcn))

				$commandOptions["--" . CloneModuleCommand::DESCRIPTOR_OPTION] = $descriptorFqcn;

			$commandInput = new ArrayInput($commandOptions);

			return $command->run($commandInput, $output);
		}
	}
?>