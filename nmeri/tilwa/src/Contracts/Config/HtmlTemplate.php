<?php

	namespace Tilwa\Contracts\Config;

	interface HtmlTemplate extends ConfigMarker {

		public function getViewPath():array;

		public function setViewPath(string $path):void;
	}
?>