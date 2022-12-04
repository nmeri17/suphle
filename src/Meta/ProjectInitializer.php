<?php
	namespace Suphle\Meta;

	use Suphle\Contracts\ConsoleClient;

	use Suphle\Modules\Commands\CloneModuleCommand;

	use Suphle\Server\VendorBin;

	use Symfony\Component\Process\Process;

	class ProjectInitializer {

		private ?Process $runningProcess = null;

		public function __construct (

			private readonly ConsoleClient $consoleClient,

			private readonly VendorBin $vendorBin
		) {

			//
		}

		public function allInitOperations (string $moduleName, string $descriptorFqcn = null):int {

			$creationStatus = $this->createModule($moduleName, $descriptorFqcn);

			$this->vendorBin->setProcessArguments("rr", ["get-binary"])->run(); // this won't run if binary already exists

			$this->runningProcess = $this->vendorBin->getServerLauncher(

				"../../dev-rr.yaml"
			);

			$this->runningProcess->start();

			return $creationStatus;
		}

		/**
		 * Can't automate test for this until a library for adding new descriptor to app module list is found
		*/
		protected function createModule (string $moduleName, ?string $descriptorFqcn):int {

			$output = null;
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

		public function getRunningProcess ():?Process {

			return $this->runningProcess;
		}

		public function contributorOperations (string $testsPath):void {

			$this->vendorBin->setProcessArguments("rr", ["get-binary"])->run();

			$this->vendorBin->setProcessArguments("phpunit", [$testsPath])->run();
		}
	}
?>