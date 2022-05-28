<?php
	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\ModuleFiles;

	use Tilwa\File\FileSystemReader;

	/**
	 * Note: Returned paths have trailing slashes
	*/
	class AscendingHierarchy implements ModuleFiles {

		private $descriptorPath, $systemReader;

		public function __construct (string $descriptorPath, FileSystemReader $systemReader) {

			$this->descriptorPath = $descriptorPath;

			$this->systemReader = $systemReader;
		}

		public function getRootPath ():string {

			return $this->systemReader->pathFromLevels(

				$this->descriptorPath, "", 2
			);
		}

		public function activeModulePath ():string {

			return $this->systemReader->pathFromLevels(

				$this->descriptorPath, "", 1
			);
		}

		public function getViewPath ():string {

			return $this->activeModulePath() . DIRECTORY_SEPARATOR . "Markup" . DIRECTORY_SEPARATOR;
		}

		public function getImagePath ():string {

			return "images" . DIRECTORY_SEPARATOR;
		}
	}
?>