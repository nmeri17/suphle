<?php
	namespace Suphle\Server;

	class HttpServerOperations {

		public function __construct (

			private readonly DependencySanitizer $sanitizer,

			private readonly VendorBin $vendorBin
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

			$this->vendorBin->setProcessArguments("rr", $commandOptions)

			->start($this->vendorBin->processOut(...));
		}
	}
?>