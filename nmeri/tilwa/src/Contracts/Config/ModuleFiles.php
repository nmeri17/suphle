<?php
	namespace Tilwa\Contracts\Config;

	interface ModuleFiles extends ConfigMarker {

		public function getRootPath ():string; // can't depend on a value in .env since this value determines where .env is. Will usually be two steps above below

		public function activeModulePath ():string; // dirname(__DIR__, 1) . DIRECTORY_SEPARATOR; // up one folder

		// this->activeModulePath(). DIRECTORY_SEPARATOR) . 'views'
		public function getViewPath():string;

		public function getImagePath ():string;
	}
?>