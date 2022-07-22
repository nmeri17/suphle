<?php
	namespace Suphle\Contracts\Config;

	interface Database extends ConfigMarker {

		public function getCredentials ():array;
	}
?>