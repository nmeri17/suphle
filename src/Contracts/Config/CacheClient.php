<?php
	namespace Suphle\Contracts\Config;

	interface CacheClient extends ConfigMarker {

		public function getCredentials ():array;
	}
?>