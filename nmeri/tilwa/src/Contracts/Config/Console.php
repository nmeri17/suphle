<?php
	namespace Tilwa\Contracts\Config;

	interface Console extends ConfigMarker {

		public function commandsList ():array;
	}
?>