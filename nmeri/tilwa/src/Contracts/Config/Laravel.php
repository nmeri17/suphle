<?php
	namespace Tilwa\Contracts\Config;

	interface Laravel extends ConfigMarker {

		// [configName => My\Tilwa\Config::class]
		public function configBridge ():array;

		// [concrete::class => provider]
		public function getProviders ():array;

		public function hasRoutes():bool;

		public function usesPackages ():bool;
	}
?>