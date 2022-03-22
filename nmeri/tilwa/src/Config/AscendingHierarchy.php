<?php
	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\ModuleFiles;

	/**
	 * Note: Returned paths have trailing slashes
	*/
	class AscendingHierarchy implements ModuleFiles {

		private $descriptorPath;

		public function __construct (string $descriptorPath) {

			$this->descriptorPath = $descriptorPath;
		}

		public function getRootPath ():string {

			return dirname($this->descriptorPath, 2) . DIRECTORY_SEPARATOR;
		}

		public function activeModulePath ():string {

			return dirname($this->descriptorPath, 1) . DIRECTORY_SEPARATOR;
		}

		public function getViewPath ():string {

			return $this->activeModulePath() . DIRECTORY_SEPARATOR . "Markup" . DIRECTORY_SEPARATOR;
		}

		public function getImagePath ():string {

			return "images" . DIRECTORY_SEPARATOR;
		}
	}
?>