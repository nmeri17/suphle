<?php
	namespace Suphle\Contracts\Config;

	interface Console extends ConfigMarker {

		public function commandsList ():array;
	}
?>