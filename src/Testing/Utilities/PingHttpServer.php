<?php
	namespace Suphle\Testing\Utilities;

	use Suphle\Contracts\Config\ModuleFiles;

	use Suphle\Server\Commands\HttpServerCommand;

	use Symfony\Component\Process\Process;

	trait PingHttpServer {

		protected int $setupDuration = 20_000;

		protected string $modulesFolder = "AllModules";

		// Using a process over a command since that would warrant re-specifying modules map, and doesn't guarantee to turn off the rr process if successful

		// Not automating tests for this asserter since some of the bootstrap operations will attempt to scan the entire codebase, which will not only fail but take an awful amount of time. But it's *manually* tested on the starter project

		/**
		 * @param {binaryPath}: Location of the Suphle executable
		 * @param {configPath}: When present, it's not necessary to be defined inside the options array
		*/
		protected function assertServerBuilds (
			array $userDefinedOptions = [], string $binaryPath = null,

			string $configPath = null
		):void {

			if (is_null($binaryPath))

				$binaryPath = $this->getContainer()->getClass(ModuleFiles::class)
				
				->getRootPath();

			$serverDefaults = [

				"php", "suphle", HttpServerCommand::commandSignature()
			];

			if (is_null($configPath))

				$configPath = $binaryPath. "dev-rr.yaml";

			$overwritable = [];

			foreach (array_merge([

				HttpServerCommand::MODULES_FOLDER_ARGUMENT => $this->modulesFolder,

				HttpServerCommand::RR_CONFIG_ARGUMENT => $configPath,

				"--". HttpServerCommand::IGNORE_STATIC_FAILURE_OPTION => null
			], $userDefinedOptions) as $key => $value) {

				if (str_starts_with($key, "--")) {

					$entry = $key;

					if (!is_null($value)) $entry .= "=$value";
				}

				else $entry = $value;

				$overwritable[] = $entry;
			}

			$serverOptions = array_merge($serverDefaults, $overwritable);
		
			$serverProcess = new Process($serverOptions, $binaryPath);

			$serverProcess->setTimeout($this->setupDuration);

			$serverProcess->start();

			$this->assertTrue(

				$this->serverIsReady($serverProcess),

				"Unable to start server:\n".

				$this->processFullOutput($serverProcess)
			);

			$serverProcess->stop();
		}

		/**
		 * This method is blocking (like a while loop) and will continually poll the process until the internal condition is met. It doesn't have anything to do with timeout/asyncronicity, only lets us know the appropriate time to make assertions against the process i.e. when condition is met
		*/
		private function serverIsReady (Process $serverProcess):bool {

			$serverProcess->waitUntil(function ($type, $buffer) {

				return stripos((string) $buffer, "http server was started");
			});

			return $serverProcess->isRunning(); // If condition is not met, process should be released by timing out. Thus, this returns false
		}

		private function processFullOutput (Process $process):string {

			return $process->getOutput() . "\n".

			$process->getErrorOutput();
		}
	}
?>