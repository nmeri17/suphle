<?php
	namespace Tilwa\Contracts\Auth;

	interface ModelAuthorities {
		
		public function retrieved ($entity):bool;

		public function updating ($entity):bool;

		/**
		 * @param {entity} May have no id
		*/
		public function creating ($entity):bool;

		public function deleting ($entity):bool;
	}
?>