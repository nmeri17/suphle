<?php
	namespace Suphle\Meta;

	use Suphle\Contracts\ConsoleClient;

	use Suphle\Modules\Commands\CloneModuleCommand;

	use Suphle\Server\VendorBin;

	use Symfony\Component\{Process\Process, Console\Output\OutputInterface};

	use Symfony\Component\Console\Input\ArrayInput;

	class ProjectInitializer {

		public const SYNC_TESTER = "phpunit",

		ASYNC_TESTER = "paratest";

		protected ?Process $runningProcess = null;

		protected string $projectRootPath;

		public function __construct (

			protected readonly ConsoleClient $consoleClient,

			protected readonly VendorBin $vendorBin
		) {

			//
		}

		public function allInitOperations (string $moduleName, string $descriptorFqcn = null, OutputInterface $output):int {

			$this->downloadRRBinary();

			$creationStatus = $this->createModule($moduleName, $descriptorFqcn, $output);

			$this->runningProcess = $this->vendorBin->getServerLauncher(

				"../../dev-rr.yaml"
			);

			$this->runningProcess->start($this->vendorBin->processOut(...));

			return $creationStatus;
		}

		/**
		 * Can't automate test for this until a library for adding new descriptor to app module list is found
		*/
		public function createModule (string $moduleName, ?string $descriptorFqcn, OutputInterface $output):int {

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

		protected function downloadRRBinary ():void {

			$commandSignature = "get-binary";

			$this->vendorBin->setProcessArguments("rr", [$commandSignature])

			->run(function ($type, $buffer) use ($commandSignature) {

				$content = "unknown command '$commandSignature'";

				if (preg_match("/$content/i", $buffer) !== 0)

					echo $buffer;
			});
		}

		public function getRunningProcess ():?Process {

			return $this->runningProcess;
		}

		public function sendRootPath ( string $executionPath):self {

			$this->vendorBin->setRootPath($executionPath);

			$this->projectRootPath = $executionPath;

			return $this;
		}

		public function contributorOperations (?string $testsPath, array $phpUnitOptions, bool $useParallel):void {

			$this->downloadRRBinary();

			$processName = !$useParallel ? self::SYNC_TESTER: self::ASYNC_TESTER;

			$testProcess = $this->vendorBin->setProcessArguments($processName, [

				$testsPath ??

				$this->projectRootPath . DIRECTORY_SEPARATOR . "tests",

				...$phpUnitOptions
			], false);

			$testProcess->setTimeout(0);

			$testProcess->run($this->vendorBin->processOut(...));
		}
	}
?>