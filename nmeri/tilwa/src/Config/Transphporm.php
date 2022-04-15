<?php
	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\{Transphporm as TConfig, ModuleFiles};

	class Transphporm implements TConfig {

		private $fileConfig;

		public function __construct (ModuleFiles $fileConfig) {

			$this->fileConfig = $fileConfig;
		}
		
		public function getTssPath ():string {

			return $this->fileConfig->activeModulePath() . "Tss" . DIRECTORY_SEPARATOR;
		}

		public function inferFromViewName ():bool {

			return true;
		}
	}
?>