<?php
	namespace Tilwa\Contracts\Config;

	interface ModuleFiles extends ConfigMarker {

		public function getRootPath ():string;

		public function activeModulePath ():string;

		public function getViewPath():string;

		public function getImagePath ():string;
	}
?>