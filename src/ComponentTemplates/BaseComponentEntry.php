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

				if (file_exists(

					$destination . DIRECTORY_SEPARATOR . $this->prefixName()
				))

					return true;

			return false;
		}

		public function eject ():void {

			foreach ($this->getSources() as $sourceFolder => $destination)

				copy(
					$sourceFolder,

					$destination . DIRECTORY_SEPARATOR . $this->prefixName()
				);
		}

		abstract protected function prefixName ():string;

		/**
		 * [prefixName] will be auto-appended for you
		*/
		abstract protected function getSources ():array;
	}
?>