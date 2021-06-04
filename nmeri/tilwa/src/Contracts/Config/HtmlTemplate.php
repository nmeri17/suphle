<?php

	namespace Tilwa\Contracts\Config;

	interface HtmlTemplate extends ConfigMarker {

		public function getViewPaths():array;

		public function addViewPath(string $path):void; // viewPaths[] =
	}
?>