<?php

	namespace Tilwa\Contracts\Config;

	interface ModuleFiles extends ConfigMarker {

		public function getRootPath ():string; // $_ENV['APP_BASE_PATH']

		public function activeModulePath ():string; // dirname(__DIR__)
	}
?>