<?php

	namespace Tilwa\Contracts\Config;

	interface Events extends ConfigMarker {

		// @return sub-class of EventManager where we bound listeners to events we wanna listen to
		public function getManager ():string;
	}
?>