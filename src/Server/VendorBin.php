<?php
	namespace Suphle\Server;

	use Suphle\File\FileSystemReader;

	use Symfony\Component\Process\Process;

	class VendorBin {

		protected string $projectRootPath;

		public function __construct (private readonly FileSystemReader $fileSystemReader) {

			//
		}

		public function setRootPath (string $path):void {

			$this->projectRootPath = $path;
		}

		public function getBinDir ():string {

			return $this->projectRootPath . "/vendor/bin";
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

			return $this->setProcessArguments("rr", [

				"serve", "-c", $this->getRootFile($relativeToConfig)
			]);
		}

		public function setProcessArguments (string $processName, array $commandOptions):Process {

			$process = new Process(
				array_merge([$processName], $commandOptions),

				$this->getBinDir()
			);

			$process->setTimeout(20_000);

			$process->setIdleTimeout(60);

			return $process;
		}

		public function processOut ($type, $buffer):void {

			if (Process::ERR === $type)

				echo "ERR > $buffer";

			else echo $buffer;
		}
	}
?>