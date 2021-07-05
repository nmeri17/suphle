<?php

	namespace Tilwa\Contracts\Config;

	interface Services extends ConfigMarker {

		public function lifecycle():bool;

		public function getProviders():array;

		public function usesLaravelPackages ():bool;
	}
?>