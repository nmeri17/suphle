<?php
	namespace Suphle\Contracts\Config;

	use Suphle\Contracts\Hydration\ExternalPackageManager;

	interface ContainerConfig extends ConfigMarker {

		/**
		 * @return string<ExternalPackageManager>[]
		*/
		public function getExternalHydrators ():array;
	}
?>