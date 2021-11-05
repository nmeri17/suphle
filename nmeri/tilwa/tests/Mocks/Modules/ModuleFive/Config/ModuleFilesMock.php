<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleFive\Config;

	use Tilwa\Contracts\Config\ModuleFiles;

	class ModuleFilesMock implements ModuleFiles {

		public function getRootPath ():string {

			return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;
		}

		public function activeModulePath ():string {

			return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
		}

		public function getViewPath():string {

			return $this->activeModulePath() . DIRECTORY_SEPARATOR . 'Markup';
		}
	}
?>