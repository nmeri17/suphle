<?php
	namespace Suphle\Server;

	use Suphle\File\FileSystemReader;

	use Symfony\Component\Process\Process;

	class VendorBin {

		public function __construct (private readonly FileSystemReader $fileSystemReader) {

			//
		}

		public function getBinDir ():string {

			return $_SERVER["COMPOSER_RUNTIME_BIN_DIR"];
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

			return new Process(array_merge(

				[$this->getBinDir() .$processName ], $commandOptions
			));
		}
	}
?>