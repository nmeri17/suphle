<?php
	namespace Suphle\Server;

	use Exception;

	class HttpServerOperations {

		public function __construct (

			protected readonly DependencySanitizer $sanitizer,

			protected readonly VendorBin $vendorBin,

			protected readonly PsalmWrapper $psalmWrapper
		) {

			//
		}

		public function sendRootPath (string $projectRoot, string $scannablePath):self {

			$this->sanitizer->setExecutionPath(

				$projectRoot. DIRECTORY_SEPARATOR. $scannablePath
			);

			$this->vendorBin->setRootPath($projectRoot);

			$this->psalmWrapper

			->setExecutionPath($projectRoot, $scannablePath);

			return $this;
		}

		public function runStaticChecks (bool $autoRefactor, bool $isTestBuild):void {

			if ($isTestBuild) return; // disabling scan cuz that takes quite a bit of time

			if ($this->psalmWrapper->analyzeErrorStatus([], $autoRefactor))

				return;

			$process = $this->psalmWrapper->getLastProcess();

			throw new Exception(

				$process->getOutput(). "\n". $process->getErrorOutput()
			);
		}

		public function restoreSanity ():void {

			$this->sanitizer->cleanseConsumers();
		}

		public function startRRServer (?string $configPath):void {

			$commandOptions = ["serve"];

			if (!is_null($configPath))

				$commandOptions = array_merge(

					$commandOptions, ["-c", $configPath]
				);

			$process = $this->vendorBin->setProcessArguments(VendorBin::RR_BINARY, $commandOptions, false);

			$process->setTimeout(0); // run indefinitely

			$process->start();

			$process->wait(function ($type, $buffer) { // either this or a foreach loop is required for starting the long-running process

				echo $buffer;
			});
		}
	}
?>