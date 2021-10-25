<?php
	namespace Tilwa\Contracts\Config;

	interface Services extends ConfigMarker {

		public function lifecycle():bool;

		public function getLoaders():array;

		public function usesLaravelPackages ():bool;
	}
?>