<?php
	namespace Tilwa\Contracts\Hydration\ScopeHandlers;

	interface ModifyInjected {

		/**
		 * @return wrapped object for the caller
		*/
		public function setCallDetails (object $concrete, string $caller):object;
	}
?>