<?php
	namespace Suphle\Server;

	use Suphle\Contracts\Config\ModuleFiles;

	use Symfony\Component\Process\Process;

	class PsalmWrapper {

		protected const COMMAND_NAME = "psalm";

		public const ALTER_OPTION = "--alter";

		protected ?Process $lastProcess = null;

		public function __construct (

			protected readonly ModuleFiles $fileConfig,

			protected readonly VendorBin $vendorBin
		) {

			//
		}

		public function setExecutionPath (string $path):void {

			$this->vendorBin->setRootPath($path);
		}

		public function getLastProcess ():?Process {

			return $this->lastProcess;
		}

		public function initPsalm ():self {

			$this->vendorBin->setProcessArguments(

				self::COMMAND_NAME, ["--init"]
			)->run();

			return $this;
		}

		public function scanForErrors (array $filePaths = [], bool $correctMistakes = false):bool {

			if (empty($filePaths))

				$filePaths = [$this->fileConfig->getRootPath()];

			$commandOptions = [implode(" ", ($filePaths))];

			if ($correctMistakes) $commandOptions[] = self::ALTER_OPTION;

			$this->lastProcess = $this->vendorBin->setProcessArguments(

				self::COMMAND_NAME, $commandOptions
			);

			$this->lastProcess->run();

			return $this->lastProcess->isSuccessful();
		}
	}
?>