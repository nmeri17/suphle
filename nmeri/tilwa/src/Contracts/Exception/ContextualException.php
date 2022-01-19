<?php
	namespace Tilwa\Contracts\Exception;

	interface ContextualException {

		public function getContext ():array;
	}
?>