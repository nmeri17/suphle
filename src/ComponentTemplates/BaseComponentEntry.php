<?php
	namespace Suphle\ComponentTemplates;

	use Suphle\Contracts\Config\ModuleFiles;

	abstract class BaseComponentEntry {

		protected $fileConfig;

		public function __construct (ModuleFiles $fileConfig) {

			$this->fileConfig = $fileConfig;
		}

		public function hasBeenEjected ():bool {

			return file_exists($this->userLandMirror());
		}

		/**
		 * Destination to deposit template files
		 * 
		 * @return With trailing slash
		*/
		public function userLandMirror ():string {

			return implode(DIRECTORY_SEPARATOR, [

				$this->fileConfig->activeModulePath(),

				$this->fileConfig->componentsPath(),

				get_called_class()
			]) . DIRECTORY_SEPARATOR;
		}

		public function eject ():void {

			copy($this->templatesLocation(), $this->userLandMirror());
		}

		protected function templatesLocation ():string {

			return __DIR__ . DIRECTORY_SEPARATOR . "ComponentTemplates";
		}
	}
?>