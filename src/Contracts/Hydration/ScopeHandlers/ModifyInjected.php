<?php
	namespace Suphle\Contracts\Hydration\ScopeHandlers;

	interface ModifyInjected {

		/**
		 * @return wrapped object for the caller
		*/
		public function examineInstance (object $concrete, string $caller):object;
	}
?>