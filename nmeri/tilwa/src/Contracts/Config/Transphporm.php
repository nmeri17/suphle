<?php

	namespace Tilwa\Contracts\Config;

	interface Transphporm extends ConfigMarker {

		/**
		 * @return Absolute path, with trailing slash
		*/
		public function getTssPath ():string;

		public function inferFromViewName ():bool;
	}
?>