<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Config;

	use Tilwa\Contracts\Config\ModuleFiles;

	// add this to list of installation stubs
	class ModuleFilesMock implements ModuleFiles {

		public function getRootPath ():string {

			return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
		}

		public function activeModulePath ():string {

			return dirname(__DIR__, 1) . DIRECTORY_SEPARATOR;
		}

		public function getViewPath():string {

			return $this->activeModulePath() . DIRECTORY_SEPARATOR . 'Markup';
		}

		public function getImagePath ():string {

			return "images" . DIRECTORY_SEPARATOR;
		}
	}
?>