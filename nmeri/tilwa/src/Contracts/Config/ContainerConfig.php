<?php
	namespace Tilwa\Contracts\Config;

	use Tilwa\Contracts\Hydration\ExternalPackageManager;

	interface ContainerConfig extends ConfigMarker {

		/**
		 * @return string<ExternalPackageManager>[]
		*/
		public function getExternalHydrators ():array;
	}
?>