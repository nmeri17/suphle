<?php

	namespace Tilwa\Contracts\Config;

	interface Authentication extends ConfigMarker {

		public function getUserModel():string;
	}
?>