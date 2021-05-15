<?php

	namespace Tilwa\Contracts\Config;

	interface ModuleFiles extends ConfigMarker {

		public function getRootPath ():string;
	}
?>