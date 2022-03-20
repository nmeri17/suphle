<?php

	namespace Tilwa\Contracts\Config;

	interface Transphporm extends ConfigMarker {

		public function getTssPath ():string;

		public function inferFromViewName ():bool;
	}
?>