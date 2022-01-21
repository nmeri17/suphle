<?php
	namespace Tilwa\Contracts\Hydration\ScopeHandlers;

	interface ModifyInjected {

		public function upgradeInstance ($concrete);
	}
?>