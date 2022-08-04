<?php
	namespace Suphle\ComponentTemplates;

	use Suphle\Contracts\Config\ModuleFiles;

	abstract class BaseComponentEntry {

		protected $fileConfig;

		public function __construct (ModuleFiles $fileConfig) {

			$this->fileConfig = $fileConfig;
		}

		public function hasBeenEjected ():bool {

			foreach ($this->getSources() as $destination)

				if (file_exists($destination)) return true;

			return false;
		}

		public function eject ():void {

			foreach ($this->getSources() as $sourceFolder => $destination)

				copy($sourceFolder, $destination);
		}

		abstract protected function prefixName ():string;

		abstract protected function getSources ():array;
	}
?>