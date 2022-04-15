<?php
	namespace Tilwa\Contracts\Hydration;

	interface ClassHydrationBehavior {

		public function protectRefreshPurge (string $purger):bool;
	}
?>