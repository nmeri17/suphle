<?php
	namespace Tilwa\Contracts\Config;

	interface Laravel extends ConfigMarker {

		// [configName => My\Tilwa\Config::class]
		public function configBridge ():array;

		// [concrete::class => provider]
		public function getProviders ():array;

		// @return names of providers that register routes
		public function registersRoutes ():array;

		public function usesPackages ():bool;

		public function frameworkDirectory ():string;
	}
?>