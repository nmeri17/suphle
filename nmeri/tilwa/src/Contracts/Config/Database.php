<?php
	namespace Tilwa\Contracts\Config;

	interface Database extends ConfigMarker {

		public function getCredentials ():array;
	}
?>