<?php
	namespace Suphle\Server;

	class HttpServerOperations {

		public function __construct (

			protected readonly DependencySanitizer $sanitizer,

			protected readonly VendorBin $vendorBin
		) {

			//
		}

		public function sendRootPath (string $path):self {

			$this->sanitizer->setExecutionPath($path);

			$this->vendorBin->setRootPath($path);

			return $this;
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

			$process = $this->vendorBin->setProcessArguments("rr", $commandOptions, false);

			$process->setTimeout(0); // run indefinitely

			$process->start();

			$process->wait(function ($type, $buffer) { // either this or a foreach loop is required for starting the long-running process

				echo $buffer;
			});
		}
	}
?>