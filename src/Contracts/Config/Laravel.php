<?php
	namespace Suphle\Contracts\Config;

	interface Laravel extends ConfigMarker {

		/**
		 * [configName => My\Suphle\Config::class]
		*/
		public function configBridge ():array;

		public function registersRoutes ():bool;
	}
?>