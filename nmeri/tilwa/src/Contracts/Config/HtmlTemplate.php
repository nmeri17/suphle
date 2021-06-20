<?php

	namespace Tilwa\Contracts\Config;

	interface HtmlTemplate extends ConfigMarker {

		// ModuleFiles->activeModulePath(). DIRECTORY_SEPARATOR) . 'views'
		public function getViewPath():array;
	}
?>