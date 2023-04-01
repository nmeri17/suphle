<?php
	namespace Suphle\Server;

	use Suphle\File\FileSystemReader;

	use Symfony\Component\Process\Process;

	class VendorBin {

		public const RR_BINARY = "rr";

		protected string $projectRootPath;

		public function __construct (protected readonly FileSystemReader $fileSystemReader) {

			//
		}

		public function setRootPath (string $path):void {

			$this->projectRootPath = $path;
		}

		public function getBinDir ():string {

			return $this->fileSystemReader->noTrailingSlash($this->projectRootPath) . "/vendor/bin";
		}

		/**
		 * @param {relativePath} Path as relative to vendor/bin
		*/
		public function getRootFile (string $relativePath):string {

			return $this->fileSystemReader->getAbsolutePath(

				$this->getBinDir(), $relativePath
			);
		}

		public function getServerLauncher (string $relativeToConfig):Process {

			return $this->setProcessArguments(self::RR_BINARY, [

				"serve", "-c", $this->getRootFile($relativeToConfig)
			]);
		}

		public function setProcessArguments (string $processName, array $commandOptions, bool $withTimeout = true):Process {

			$process = new Process(
				array_merge([$processName], $commandOptions),

				$this->getBinDir()
			);

			if ($withTimeout) $process->setTimeout(20_000);

			return $process;
		}

		public function processOut ($type, $buffer):void {

			if (Process::ERR === $type)

				echo "ERROR > $buffer";

			else echo $buffer;
		}
	}
?>