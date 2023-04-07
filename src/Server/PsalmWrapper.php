<?php
	namespace Suphle\Server;

	use Suphle\Contracts\Config\ModuleFiles;

	use Symfony\Component\Process\Process;

	class PsalmWrapper {

		protected const COMMAND_NAME = "psalm";

		public const ALTER_COMMAND = "psalter";

		protected ?Process $lastProcess = null;

		protected string $relativePathToScan;

		public function __construct (

			protected readonly ModuleFiles $fileConfig,

			protected readonly VendorBin $vendorBin
		) {

			//
		}

		public function setExecutionPath (string $path, string $relativePathToScan):void {

			$this->vendorBin->setRootPath($path);

			$this->relativePathToScan = $relativePathToScan;
		}

		public function getLastProcess ():?Process {

			return $this->lastProcess;
		}

		public function analyzeErrorStatus (array $filePaths = [], bool $correctMistakes = false):bool {

			if (!$correctMistakes) {

					$processName = self::COMMAND_NAME;

				if (empty($filePaths))

					$filePaths = [

						$this->fileConfig->getRootPath(). $this->relativePathToScan
					];

				$commandOptions = [implode(" ", ($filePaths))];
			}

			else {

				$processName = self::ALTER_COMMAND;

				$commandOptions = [

					"--issues=all",

					"--allow-backwards-incompatible-changes=false",

					"--safe-types=true"
				];
			}

			$this->lastProcess = $this->vendorBin->setProcessArguments(

				$processName, $commandOptions
			);

			$this->lastProcess->run(function ($type, $buffer) {

				echo $buffer;
			});

			return $this->lastProcess->isSuccessful();
		}
	}
?>