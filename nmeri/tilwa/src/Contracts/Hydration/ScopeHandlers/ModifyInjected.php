<?php
	namespace Tilwa\Contracts\Hydration\ScopeHandlers;

	interface ModifyInjected {

		/**
		 * @return wrapped object for the caller
		*/
		public function proxifyInstance ($concrete, string $caller);
	}
?>